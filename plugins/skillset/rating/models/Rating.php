<?php namespace skillset\Rating\Models;

use Carbon\Carbon;
use Cms\Traits\ApiResponser;
use Cms\Traits\Pagination;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Model;
use RainLab\User\Models\User;
use skillset\Configuration\Traits\Config;
use skillset\Orders\Models\Order;
use skillset\Jobs\Models\Order as JobOrder;
use skillset\Marketplace\Models\Order as MarketplaceOrder;
use skillset\Services\Models\Service;

/**
 * Model
 */
class Rating extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use Pagination;
    use Config;
    use ApiResponser;
    
    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = true;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'skillset_rating_';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    protected $visible = ['id','rate', 'comment', 'created_at', 'rater_id', 'User'];

//    protected $fillable = ['order_id', 'rated_id', 'rater_id'];

    public $orderTypes = [
        'order' => 1,
        'jobOrder' => 2,
        'marketplaceOrder' => 3
    ];

    public $belongsTo = [
        'RatedUser'         => [User::class, 'key' => 'rated_id', 'otherKey' => 'id', 'conditions' => 'status_id = 1 OR status_id = 2  AND admin_user_id is null'],
        'RaterUser'         => [User::class, 'key' => 'rater_id', 'otherKey' => 'id', 'conditions' => 'status_id = 1 OR status_id = 2 AND admin_user_id is null'],
        'Order'             => [Order::class],
        'jobOrder'          => [JobOrder::class, 'key' => 'order_id', 'otherKey' => 'id'],
        'marketplaceOrder'  => [MarketplaceOrder::class, 'key' => 'order_id', 'otherKey' => 'id']

    ];

    public function User()
    {
        return $this->hasOne(User::class, 'id', 'rater_id');
    }

    public function Rater()
    {
        return $this->hasOne(User::class, 'id', 'rater_id');
    }
    public function Rated()
    {
        return $this->hasOne(User::class, 'id', 'rated_id');
    }

    public function beforeSave()
    {
        $request = request();
        if (!$request->url) {
            return;
        }
        $queryParams = explode('/',$request->url);
        $orderTypesByQuery = [
            'orders' => 1,
            'jobs' => 2,
            'Marketplace' => 3
        ];
        $this->order_type = Arr::get($orderTypesByQuery, $queryParams[2]);
        $this->status_id = 1;

    }

    public function afterSave()
    {
        $request = request();
        if (!$request->url) {
            return;
        }
        $this->UpdateOrderUsersRates($this->order_id, $this->order_type);
    }

    public function afterDelete()
    {
        $request = request();
        if (!$request->url) {
            return;
        }
        $this->UpdateOrderUsersRates($this->order_id, $this->order_type);

    }

    public function getAll($params = [])
    {
        $Query = self::with('User')->where('rated_id', Arr::get($params, 'user_id'))->where('status_id', 1);
        $Count = $Query->count();
        $Pager = $this->GetPageData($Count, Arr::get($params, 'Limit', 40), Arr::get($params, 'Page', 1));
        $Query->orderBy('id', 'desc')->limit(Arr::get($Pager, 'limit', 0))
            ->offset(Arr::get($Pager, 'offset', 0));
        $UserModel = (new User);
        $Data = $Query->get()->map(function($item) use ($UserModel) {
            $Data = $item->toArray();
            $Data['user']   = $UserModel->filterInfo($item->User);
            return $Data;
        });
        $Return['rating'] = $Data->toArray();
        if (Arr::get($params, 'with_pagination')) {
            $Return['pagination'] = $Pager;
        }
        return $Return;
    }

    public function rateUser($params = [])
    {
        $orderType = Arr::get($params, 'order_type', $this->orderTypes['order']);

        $this->ValidateRate(
            config('auth.UserID'),
            Arr::get($params, 'user_id'),
            Arr::get($params, 'order_id'),
            $orderType
        );

        $Rate = self::create([
            'order_type' => $orderType,
            'order_id'   => Arr::get($params, 'order_id'),
            'rater_id'   => config('auth.UserID'),
            'rated_id'   => Arr::get($params, 'user_id'),
            'rate'       => Arr::get($params, 'rate'),
            'comment'    => Arr::get($params, 'comment'),
            'status_id'  => 0
        ]);

        $RatesCount = self::where('order_id', Arr::get($params, 'order_id'))->count();

        if ($RatesCount >= 2) {
            self::where('order_type', $orderType)
                ->where('order_id', Arr::get($params, 'order_id'))
                ->update(['status_id' => 1]);

            $this->UpdateOrderUsersRates(Arr::get($params, 'order_id'), $orderType);
        }

        return $Rate->toArray();
    }

    private function ValidateRate($RaterID, $RatedID, $OrderID, $orderType)
    {
        switch ($orderType) {
            case $this->orderTypes['order']:
                $orderModel = new Order();

                $order = $orderModel->where('id', $OrderID)
                    ->where(function ($q) use ($RaterID, $RatedID) {
                        $q->orWhere(function ($q) use ($RaterID, $RatedID) {
                            $q->where('client_id', $RaterID);
                            $q->where('worker_id', $RatedID);
                        });
                        $q->orWhere(function ($q) use ($RaterID, $RatedID) {
                            $q->where('client_id', $RatedID);
                            $q->where('worker_id', $RaterID);
                        });
                    })
                    ->where('status_id', $orderModel->statuses['user_payed'])
                    ->first();
                break;
            case $this->orderTypes['jobOrder']:
                $orderModel = new JobOrder();
                $order = $orderModel->where('id', $OrderID)
                    ->where('status', $orderModel->statuses['paid'])
                    ->first();

                if ($order){
                    $client = $orderModel->getUserByRole($order, 'client');
                    $worker = $orderModel->getUserByRole($order, 'worker');

                    if (
                        !(($client->id == $RaterID && $worker->id == $RatedID)
                            || ($client->id == $RatedID && $worker->id == $RaterID))
                    ) {
                        throw new \Exception('not_allowed', self::$ERROR_CODES['FORBIDDEN']);
                    }
                }

                break;
            case $this->orderTypes['marketplaceOrder']:
                $orderModel = new MarketplaceOrder();
                $order = $orderModel->where('id', $OrderID)
                    ->where('status', $orderModel->statuses['paid'])
                    ->first();

                if ($order){
                    $client = $orderModel->getUserByRole($order, 'client');
                    $seller = $orderModel->getUserByRole($order, 'seller');

                    if (
                        !(($client->id == $RaterID && $seller->id == $RatedID)
                            || ($client->id == $RatedID && $seller->id == $RaterID))
                    ) {
                        throw new \Exception('not_allowed', self::$ERROR_CODES['FORBIDDEN']);
                    }
                }

                break;
            default:
                $order = null;
                break;
        }

        if (!$order) {
            throw new \Exception('not_allowed', self::$ERROR_CODES['FORBIDDEN']);
        }
        $AlreadyRated = self::where('rater_id', $RaterID)
            ->where('order_type', $orderType)
            ->where('order_id', $OrderID)
            ->first();

        if ($AlreadyRated) {
            throw new \Exception('already_rated', self::$ERROR_CODES['FORBIDDEN']);
        }
        return true;
    }

    private function UpdateOrderUsersRates($OrderID, $orderType)
    {
        $OrderRates = self::where('order_type', $orderType)
            ->where('order_id', $OrderID)
            ->get();


        foreach ($OrderRates AS $OrderRate) {
            $this->UpdateUserRates(Arr::get($OrderRate, 'rated_id'));
        }

        $this->makeOrderRated($OrderID, $orderType);
    }

    private function UpdateUserRates($UserID)
    {
        $rate = self::where('rated_id', $UserID)->where('status_id', 1)->selectRaw('COUNT(0) AS cnt, sum(rate)/count(0) AS rate')->first();
        (New User)->where('id', $UserID)->update(['rate' => number_format($rate->rate, 1), 'rates_count' => $rate->cnt]);
    }

    private function makeOrderRated($OrderID, $orderType)
    {
        switch ($orderType) {
            case $this->orderTypes['order']:
                $order = (new Order)->find($OrderID);
                break;
            case $this->orderTypes['jobOrder']:
                $order = (new JobOrder)->find($OrderID);
                break;
            case $this->orderTypes['marketplaceOrder']:
                $order = (new MarketplaceOrder)->find($OrderID);
                break;
            default:
                $order = null;
                break;
        }

        if ($order) {
            $order->update(['rated' => 1]);
        }
    }

    public function makeOldReviewsActive()
    {
        $minimumDate = Carbon::now()->subDays($this->getConfig('make_rate_active_after'))->toDateTimeString();

        $this->makeOldOrdersRated($minimumDate);

        $OldReviews = self::where('status_id', 0)->where('created_at', '<', $minimumDate)->get();

        foreach ($OldReviews AS $OldReview) {
            $OldReview->update(['status_id' => 1]);
            $this->makeOrderRated($OldReview->order_id, $OldReview->order_type);
            $this->UpdateUserRates($OldReview->rated_id);
        }
    }

    private function makeOldOrdersRated($minimumDate)
    {
        $OrderModel = (new Order);
        $OldOrders = $OrderModel::with('Rates')->where('status_id', $OrderModel->statuses['user_payed'])->where('rated', 0)->where('ended_at', '<', $minimumDate)->get();
        foreach ($OldOrders AS $Order) {
            $Order->update(['rated' => 1]);
            foreach ($Order->Rates AS $Rate) {
                $Rate->update(['status_id' => 1]);
                $this->UpdateUserRates($Rate->rated_id);
            }
        }
    }
}
