<?php

namespace RainLab\User\Models;

use Carbon\Carbon;
use Cms\Traits\ApiResponser;
use Illuminate\Support\Facades\DB;
use Model;
use Illuminate\Support\Arr;
use Pheanstalk\Exception;
use skillset\Configuration\Traits\Config;
use skillset\Notifications\Models\Notification;
use skillset\Orders\Models\Order;
use skillset\Payments\Models\Payment;
use skillset\Services\Models\ServiceToUser;
use skillset\Services\Models\SubService;
use Cms\Traits\Pagination;

class Worker extends Model
{
    use Pagination;
    use ApiResponser;
    use Config;
    /**
     * @var string The database table used by the model.
     */
    protected $table = 'users';
    protected $primaryKey = 'id';
    private $statuses = [
        ''
    ];


    public function getAll($params = [], $solid = false)
    {
        $Query = (new User())::where('user_type', 1)->where('status_id', '>', 0)->where('is_unactive', 0);
        $this->filter($params, $Query);

        if ($solid) {
            return $Query->pluck('id')->toArray();
        }

        $Query->with('SubServicesToUser', 'avatar')->select('users.*');

        $this->sort($params, $Query);
        $Pager = [];
        if (!Arr::get($params, 'secret') || Arr::get($params, 'secret') != config('app.admin_secret')) {
            $Count = $Query->count();
            $Pager = $this->GetPageData($Count, Arr::get($params, 'limit', 20), Arr::get($params, 'page', 1));

            $Query->limit(Arr::get($Pager, 'limit', 0))
                ->offset(Arr::get($Pager, 'offset', 0));
        }
        $UserModel = (new User);
        $Data = $Query->get()->map(function($data) use ($UserModel, $params) {
            return array_merge($UserModel->filterInfo($data), $this->generateWorkerPrices($data->SubServicesToUser, $params));
        });


        return [
            'workers'       => $Data->toArray(),
            'pagination'    => $Pager
        ];
    }

    private function filter($params, &$Query)
    {
        $subServiceIDs = [];
        $ServiceIDs = [];
        if (Arr::get($params,'service_sub_ids')) {
            $subServiceIDs = explode('.', Arr::get($params,'service_sub_ids'));
            $Query->whereHas('ServiceToUser', function($q) use ($subServiceIDs) {
                $q->whereIn('services_sub_id', $subServiceIDs);
            });
        }

        if (Arr::get($params,'service_ids')) {
            $ServiceIDs = explode('.', Arr::get($params,'service_ids'));
            $Query->whereHas('SubServies', function($q) use ($ServiceIDs) {
                $q->whereIn('skillset_services_sub.service_id', $ServiceIDs);
            });
        }

        if ($CountryID = Arr::get($params,'country_id')) {
            $Query->where('country_id', $CountryID);
        }

        if ($RegionIDs = Arr::get($params,'region_ids')) {
            $Query->whereIn('region_id', explode('.',$RegionIDs));
        }
        $PriceFrom = Arr::get($params,'price_from');
        $PriceTo = Arr::get($params,'price_to');
        if ($PriceFrom || $PriceTo) {
            $Query->whereHas('ServiceToUser', function($q) use ($PriceFrom, $PriceTo, $subServiceIDs, $ServiceIDs) {
                if ($PriceFrom > 0) $q->where('price_from', '>=', $PriceFrom);
                if ($PriceTo > 0) $q->where('price_to', '<=', $PriceTo);
                if (!empty($subServiceIDs)) $q->whereIn('services_sub_id', $subServiceIDs);
                if (!empty($ServiceIDs)) {
                    $q->whereHas('SubServicePlain', function($q) use ($ServiceIDs){
                        $q->whereIn('service_id', $ServiceIDs);
                    });
                }
            });
        }
//
    }

    private function sort($params, &$Query)
    {

        if (in_array(Arr::get($params,'sort'), [1, 2])) {
//            $Query->leftJoin('skillset_services_sub_to_user AS ssstu', function ($join) use ($params){
//                $join->on('users.id', '=', 'ssstu.user_id');
//                $join->where('ssstu.id', '=', DB::raw($this->getSortJoinQuery($params)));
//            });
            $Query->join(DB::Raw($this->getSortJoinQuery($params)), 'users.id', '=', 'ssstu.user_id');
        }

        switch (Arr::get($params,'sort')) {
            case 1:
                $Query->orderByRaw('ssstu.price_from asc');
                break;
            case 2:
                $Query->orderByRaw('ssstu.price_from desc');
                break;
            case 3:
                $Query->orderBy('rate', 'desc');
                break;
            default:
                $Query->orderBy('id', 'desc');
        }
    }

    private function generateWorkerPrices($SubServies, $params)
    {
        $Data = $SubServies->filter(function ($service) use ($params) {
            $Response = true;
            if (Arr::get($params, 'service_ids')) {
                $Response = $service->SubServicePlain && in_array($service->SubServicePlain->service_id, explode('.',Arr::get($params, 'service_ids')));
            }
            if (Arr::get($params, 'service_sub_ids') && $Response) {
                $Response = $service->SubServicePlain && in_array($service->SubServicePlain->id, explode('.', Arr::get($params, 'service_sub_ids')));
            }
            if (Arr::get($params, 'price_from') && $Response) {
                $Response = $service->price_from >= Arr::get($params,'price_from');
            }
            if (Arr::get($params, 'price_to') && $Response) {
                $Response = $service->price_to <= Arr::get($params,'price_to');
            }

            return $Response;
        });

        return [
            'price_from' => (Arr::get($params, 'sort', 1) == 1 ? $Data->min('price_from') : $Data->max('price_from')) ?? 0,
            'price_to' => $Data->max('price_to') ?? 0
        ];
    }

    public function fillBalance($params = [])
    {
        return (new Payment)->fillBalance(config('auth.UserID'), Arr::get($params, 'amount'));
    }

    public function updateBalance($user_id, $price, $add = true)
    {
        $User = self::find($user_id);
        $User->balance = $add ? ($User->balance + $price) : ($User->balance - $price);
        $User->save();
        if ($add AND $User->balance >= 0) {
            $User->update(['is_unactive' => 0]);

            (new Notification())->sendTemplateNotifications($User->id, 'userActivated');
        }
    }

    public function checkWorkerIDs($workerIDs)
    {
        return (new User)->whereIn('id', $workerIDs)
            ->where('user_type', 1)->where('is_busy', 0)->where('status_id', '>', 0)
            ->where('is_unactive', 0)->pluck('id')->toArray();
    }

    public function makeWorkersUnActive()
    {
        $OrderModel = (new Order);
        $minimumDate = Carbon::now()->subDays(1)->toDateTimeString();
        $fromDate = Carbon::now()->subDays(1)->subMinutes(10)->toDateTimeString();
        $UserIDs = (new User)->where('user_type', 1)->where('is_unactive', 0)->where('balance', '<', 0)
            ->join('skillset_orders_ AS so', function ($join) use ($OrderModel, $minimumDate, $fromDate) {
            $join->on('users.id', '=', 'so.worker_id')
                ->where('so.status_id', '=', $OrderModel->statuses['user_payed'])
                ->where('so.updated_at', '<=', $minimumDate)
                ->where('so.updated_at', '>=', $fromDate);
            })->pluck('users.id')->toArray();
        if (!$UserIDs OR empty($UserIDs)) {
            return;
        }
        (new User)->whereIn('id', $UserIDs)->update([
            'is_unactive' => 1
        ]);

    }

    private function getSortJoinQuery($params)
    {
        $joinQuery = DB::query();
        $joinQuery->select('ssstu.user_id', DB::raw((Arr::get($params,'sort') == 1 ? 'min(price_from)' : 'max(price_from)').' price_from'))->from(DB::raw('skillset_services_sub_to_user ssstu'))
        ->join('skillset_services_sub AS ss', 'ss.id', 'ssstu.services_sub_id')
        ->groupBy('ssstu.user_id');
        if (Arr::get($params,'service_sub_ids')) {
            $joinQuery->whereIn('services_sub_id', explode('.', Arr::get($params,'service_sub_ids')));
        }
        if (Arr::get($params,'service_ids')) {
            $joinQuery->whereIn('ss.service_id', explode('.', Arr::get($params,'service_ids')));
        }
        if ($PriceFrom = Arr::get($params,'price_from') > 0) $joinQuery->where('price_from', '>=', $PriceFrom);
        return '('.str_replace_array('?', $joinQuery->getBindings(), $joinQuery->toSql()).') ssstu';
    }

    public function getWorkerCommission($ID)
    {
        if (!$Worker = (new User)->find($ID)) {
            throw new Exception('Worker Not Found', self::$ERROR_CODES['NOT_FOUND']);
        }
        if ($Worker->app_commission_percent) {
            return $Worker->app_commission_percent;
        }
        if ($Worker->commission_free_till && Carbon::createFromFormat('Y-m-d H:i:s', $Worker->commission_free_till)->isFuture()) {
            return 0;
        }
        return $this->getRateCommission($Worker->rate);
    }

}
