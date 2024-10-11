<?php namespace skillset\Services\Models;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Model;
use RainLab\User\Models\User;
use skillset\details\Models\Unit;

/**
 * Model
 */
class ServiceToUser extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = true;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'skillset_services_sub_to_user';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    protected $visible = ['amount', 'unit_id', 'price_from', 'price_to', 'SubServices', 'SubServicePlain'];

    public $belongsTo = [
        'subService'    => [SubService::class, 'key' => 'services_sub_id', 'otherKey' => 'id'],
        'unit'          => [Unit::class, 'key' => 'unit_id', 'otherKey' => 'id'],
        'service'       => [Service::class, 'key' => 'service_id', 'otherKey' => 'id']
    ];


    public function getsubServiceOptions()
    {
        if ($this->service) {
            return $this->service->SubServices()->pluck('title','id')->toArray();
        }
        return (new Service)->first()->SubServices()->pluck('title','id')->toArray();
    }




    public function SubServices()
    {
        return $this->hasOne(SubService::class, 'id', 'services_sub_id')->with('Service');
    }

    public function SubServicePlain()
    {
        return $this->hasOne(SubService::class, 'id', 'services_sub_id');
    }

    public function store(array $params = [])
    {
        self::updateOrCreate([
            'user_id'           => config('auth.UserID'),
            'services_sub_id'   => Arr::get($params, 'service_sub_id'),
        ], [
            'amount'            => Arr::get($params,'amount'),
            'unit_id'           => Arr::get($params,'unit_id'),
            'price_from'        => Arr::get($params,'price_from'),
            'price_to'          => Arr::get($params,'price_to'),
            'status_id'         => config('app.statuses.active'),
            'service_id'        => Arr::get((new SubService)->find(Arr::get($params, 'service_sub_id')), 'service_id')
        ]);
    }

    public function getUserServices($UserID)
    {
        $UserServices = [];
        self::with('SubServices')
            ->where('user_id', $UserID)
            ->get()->map(function($item) use (&$UserServices) {
                $item = $item->toArray();
                $serviceID = Arr::get($item,'sub_services.service.id');
                if (!Arr::get($UserServices, $serviceID)) {
                    $UserServices[$serviceID] = Arr::get($item, 'sub_services.service');
                }
                unset($item['sub_services']['service']);
                $subService = Arr::get($item, 'sub_services');
                unset($item['sub_services']);
                $subService['user_params']  = $item;
                $UserServices[$serviceID]['sub_service'][] = $subService;
            });

        return array_values($UserServices);

    }

    public function updateUserSubServices($SubServices, $ServiceID)
    {
        $user = (new User)->find(config('auth.UserID'));
        if ($user && $user->status_id > 0) {
            $user->updated_fields = (new User)->logUpdatedFields(['services' => ''], $user);
            $user->status_id = 2;
            $user->save();
        }
        $AllServices = (new ServiceToUser)->with('SubServices')->where('user_id', config('auth.UserID'))->get()->toArray();
        $ExistingSubServiceIDs = (new ServiceToUser)
            ->where('user_id', config('auth.UserID'))
            ->whereHas('SubServicePlain', function($q) use ($ServiceID){
               $q->where('service_id', $ServiceID);
            })
            ->pluck('services_sub_id')->toArray();
        foreach ($SubServices AS $key => $subService) {
            if (Arr::get($subService, 'sub_service_title')) {
                $subService['service_sub_id'] = (New SubService)->addUsersSubService($subService, $ServiceID);
            }
            $this->store($subService);
            if (($key = array_search($subService['service_sub_id'], $ExistingSubServiceIDs)) !== false) {
                unset($ExistingSubServiceIDs[$key]);
            }
        }

        if ($ExistingSubServiceIDs) {
                self::whereIn('services_sub_id', $ExistingSubServiceIDs)->where('user_id', config('auth.UserID'))->delete();
                tracelog('Delete request : '.json_encode([
                        'userID'        => config('auth.UserID'),
                        'data before: ' => $AllServices,
                        'service id: ' => $ServiceID,
                        'input data'    => $SubServices,
                    ], JSON_UNESCAPED_UNICODE));
        }
    }

    public function listActiveServices()
    {
        // you can use distinct or groupBy
        return Service::where('status_id', 1)->pluck('title', 'id')->toArray();
    }

    public function listActiveSubServices()
    {
        $Query =  SubService::where('status_id', 1);
        $widget = \Session::get('widget', []);
        $Filters = Arr::get($widget, 'rainlab_user-Users-Filter-listFilter');
        if ($Filters) {
            $Filters = @unserialize(@base64_decode($Filters));
            if ($Filters AND Arr::get($Filters, 'scope-service', [])) {
                $ServiceIDs = array_keys(Arr::get($Filters, 'scope-service', []));
                if ($ServiceIDs) {
                    $Query = $Query->whereIn('service_id', $ServiceIDs);
                }
            }
        }

        return $Query->pluck('title', 'id')->toArray();

    }



}
