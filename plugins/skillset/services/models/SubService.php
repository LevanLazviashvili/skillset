<?php namespace skillset\Services\Models;

use Illuminate\Support\Arr;
use Model;
use RainLab\User\Models\User;

/**
 * Model
 */
class SubService extends Model
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
    public $table = 'skillset_services_sub';

    public $belongsTo = [
        'Service'    => [Service::class, 'key' => 'service_id', 'otherKey' => 'id'],
        'User'       => [User::class, 'key' => 'user_id', 'otherKey' => 'id'],
    ];

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $implement = ['RainLab.Translate.Behaviors.TranslatableModel'];

    public $translatable = ['title'];

    protected $visible = ['id','title', 'Service', 'service_id', 'default'];

    public function scopeFilterDefault($query, $parent)
    {
        return $query->where('default', 1);
    }


    public function beforeSave()
    {
        $RequestData = \request()->all();
        foreach (Arr::get($RequestData, 'RLTranslate', []) AS $item) {
            foreach ($this->translatable AS $field) {
                if (Arr::get($item, $field)) {
                    $this->slug .= Arr::get($item, $field) . '.';
                }
            }
        }
        $this->slug = substr($this->slug, 0, -1);
    }


    public function addUsersSubService($params = [], $ServiceID = 0)
    {
        $subService = self::create([
            'title'         => Arr::get($params, 'sub_service_title'),
            'service_id'    => $ServiceID,
            'user_id'       => config('auth.UserID')
        ]);

        return $subService->id;

    }

    public function getAll($params = [])
    {
        $Query = self::where(function($q){
            $q->orWhere('default', 1);
            if (config('auth.UserID')) {
                $q->orWhere('user_id', config('auth.UserID'));
            }
        });

        if ($ServiceID = Arr::get($params,'service_id')) {
            $Query->where('service_id', $ServiceID);
        }

        if ($ServiceIDs = Arr::get($params,'service_ids')) {
            $Query->whereIn('service_id', explode('.',$ServiceIDs));
        }

        if ($Keyword = Arr::get($params,'keyword')) {
            $Query->where('slug', 'like', '%'.$Keyword.'%');
        }
       return $Query->get()->toArray();
    }
}
