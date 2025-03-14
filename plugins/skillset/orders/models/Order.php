<?php namespace skillset\Orders\Models;

use Carbon\Carbon;
use Cms\Traits\ApiResponser;
use Cms\Traits\Pagination;
use Cms\Traits\SmsOffice;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Model;
use RainLab\Translate\Models\Message as TranslateMessage;
use RainLab\User\Models\User;
use RainLab\User\Models\Worker;
use skillset\Configuration\Traits\Config;
use skillset\Conversations\Models\Message;
use skillset\Notifications\Models\Notification;
use skillset\Offers\Models\Offer;
use skillset\Offers\Models\OfferWorker;
use skillset\Payments\Models\Payment;
use skillset\Rating\Models\Rating;
use skillset\Services\Models\Service;
use skillset\Services\Models\ServiceToUser;
use skillset\Services\Models\SubService;

/**
 * Model
 */
class Order extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use Pagination;
    use Config;
    use ApiResponser;
    use SmsOffice;
    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = true;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'skillset_orders_';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $statuses = [
        'work_started'            => 1,
        'work_finished_by_worker' => 2,
        'user_accepted'           => 3,
        'user_payed'              => 4

    ];
    private $availableStatuses = [
        'worker'            => [1 => [2]],
        'client'            => [2 => [3]],
        'client_pays_cash'  => [2 => [4]],
        'bank'              => [3 => [4]]
    ];

    public $paymentTypes = [
        'cash'      => 0,
        'balance'   => 1
    ];

    protected $hidden = [
        'payment_hash'
    ];

    public $belongsTo = [
        'Service'    => Service::class,
        'Client1'    => [User::class, 'key' => 'client_id', 'otherKey' => 'id'],
        'Worker1'    => [User::class, 'key' => 'worker_id', 'otherKey' => 'id'],

    ];

    public $hasMany = [
        'ordersServices' => [
            'skillset\Orders\Models\OrderService',
            'table' => 'skillset_orders_services',
            'order' => 'id'
        ],
        'ordersServicesTmp' => [
            'skillset\Orders\Models\OrderServiceTmp',
            'table' => 'skillset_orders_services_tmp',
            'order' => 'id'
        ],
        'orderRate' => [
            'skillset\rating\Models\Rating',
            'table' => 'skillset_rating_',
            'order' => 'id',
            'key' => 'order_id',
            'conditions' => 'order_type = 1'
        ],
    ];

    public function OrderServices()
    {
        return $this->HasMany(OrderService::class);
    }

    public function Worker()
    {
        return $this->hasOne(User::class, 'id', 'worker_id');
    }

    public function Client()
    {
        return $this->hasOne(User::class, 'id', 'client_id');
    }

    public function Rates()
    {
        return $this->hasMany(Rating::class, 'order_id', 'id')->where('order_type', (new Rating())->orderTypes['order']);
    }

    public function MyRate()
    {
        return $this->hasOne(Rating::class, 'order_id', 'id')->where('order_type', (new Rating())->orderTypes['order'])->where('rater_id', config('auth.UserID'));
    }

    public function getAll($params, $withPager = true, $Count = 0)
    {
        $UserModel = new User();
        $UserType = $UserModel->getUserType();
        $Query = $this->getQuery($UserType, $params);
        $Count = $Count ?: $Query->count();

        $Query->orderBy('id', 'desc');
        $Pager = $this->GetPageData($Count, Arr::get($params,'limit', 20), Arr::get($params,'page', 1));

        $Query->limit(Arr::get($Pager, 'limit', 0))
            ->offset(Arr::get($Pager, 'offset', 0));
//        $appPercent = $this->getConfig('skillset_percent'); // TODO delete
        $Data = $Query->get()->map(function ($Obj) use ($UserModel, $UserType) {
            $Return = $Obj->toArray();
            if ($UserType == 'worker' AND $Obj->Client) {
                    $Return['client'] = $Obj->Client ? $UserModel->filterInfo($Obj->Client, true, true, $Obj->custom_client_address) : '';
            } else {
                if ($Obj->seen == 0) {
                    $Obj->update(['seen' => 1]);
                }
                $Return['worker'] = $UserModel->filterInfo($Obj->Worker);
            }
//            $Return['app_percent'] = Arr::get($Return, 'app_percent') ? Arr::get($Return, 'app_percent') : $appPercent;
            $Return['available_to_rate'] = Arr::get($Return, 'status_id') == $this->statuses['user_payed'] && !Arr::get($Return, 'my_rate');

           return $Return;
        });
        $this->seenOrders();
        $Return['orders']  = $Data->toArray();
        if ($withPager) {
            $Return['pagination'] = $Pager;
        }
        return $Return;
    }

    public function getOne($id = null, $params = [])
    {
        $Obj = $this->with('Worker', 'Client', 'OrderServices', 'Rates', 'Rates.User')
            ->where($this->getWhereUser(), config('auth.UserID'));

        if (Arr::get($params, 'conversation_id')) {
            $Obj->where('conversation_id', Arr::get($params, 'conversation_id'));
        }
        if ($id) {
            $Obj = $Obj->find($id);
        } else {
            $Obj = $Obj->first();
        }
        if (!$Obj) {
            return [];
        }
        $Item = $Obj->toArray();
        $Item['client'] = (new User)->filterInfo($Obj->Client, true, true, $Obj->custom_client_address);
        $Item['worker'] = (new User)->filterInfo($Obj->Worker);
        $Item['app_percent'] = Arr::get($Item, 'app_percent') > 0 ?  Arr::get($Item, 'app_percent') : (new Worker)->getWorkerCommission($Obj->Worker->id); //TODO DELETE / EDIT
        $UserModel = (new User);
        $UserRated = false;
        foreach ($Obj->Rates AS $key => $Rate) {
            if ($Rate->rater_id == config('auth.UserID')){
                $UserRated = true;
            }
            $Item['rates'][$key]['user'] = $UserModel->filterInfo($Rate->User);
        }
        $Item['available_to_rate'] = Arr::get($Item, 'status_id') == $this->statuses['user_payed'] && !$UserRated;
        return $Item;
    }

    public function createOrder($params = [], $ByAdmin = false)
    {

        if (!$ByAdmin) {
            $this->validateCreateOrder($params);
        }
        $Order = self::create([
            'client_id'             => Arr::get($params, 'client_id', config('auth.UserID')),
            'worker_id'             => Arr::get($params, 'worker_id'),
            'title'                 => Arr::get($params, 'title'),
            'description'           => Arr::get($params, 'description'),
            'status_id'             => Arr::get($params, 'status_id'),
            'conversation_id'       => Arr::get($params, 'conversation_id'),
            'end_date'              => Arr::get($params, 'end_date'),
            'service_id'            => Arr::get($params, 'service_id'),
            'created_at'            => Carbon::now(),
            'start_date'            => Carbon::now(),
            'bank_percent'          => 0,
            'bank_percent_amount'   => 0,
            'total_price'           => 0,
            'custom_client_phone'   => $ByAdmin ? Arr::get($params, 'custom_client_phone') : '',
            'custom_client_address' => $ByAdmin ? Arr::get($params, 'custom_client_address') : '',
            'comment'               => Arr::get($params, 'comment')
        ]);
        (new User)->checkUserBusyStatus(Arr::get($params, 'worker_id'));
        return ['order_id' => $Order->id];

    }

    public function updateOrder($params = [])
    {
        $UserType = (new User)->getUserType();
        $Order = $this->where($this->getWhereUser(), config('auth.UserID'))
            ->find(Arr::get($params, 'order_id'));
        $this->validateUpdateOrder($UserType, $Order->status_id, Arr::get($params, 'status_id'));

        $availableToUpdate = ['status_id', 'price', 'total_price', 'payment_type', 'bank_percent', 'bank_percent_amount', 'app_percent', 'app_percent_amount', 'ended_at'];
        $UpdateSql = [];
        foreach ($availableToUpdate AS $key) {
            if ($value = Arr::get($params, $key)) $UpdateSql[$key] = $value;
        }
        $UpdateSql['seen'] = 0;
        $Order->update($UpdateSql);
        (new User)->checkUserBusyStatus($Order->worker_id);
        return $Order;
    }

    public function getWhereUser($UserType = null)
    {
        return ($UserType === null ? config('auth.UserType') : $UserType) == 0 ? 'client_id' : 'worker_id';
    }

    private function validateUpdateOrder($UserType, $StatusFromID, $StatusToID)
    {
        if ($StatusFromID == $StatusToID) {
            return;
        }
        $availableStatuses = Arr::get($this->availableStatuses, $UserType.'.'.$StatusFromID, []);
        if (!$availableStatuses) {
            throw new \Exception('not_allowed', self::$ERROR_CODES['FORBIDDEN']);
        }

        if (!in_array($StatusToID, $availableStatuses)) {
            throw new \Exception('not_available_status',  self::$ERROR_CODES['FORBIDDEN']);
        }
    }

    private function validateCreateOrder($params)
    {
        if ((new User)->getUserType() != 'client') {
            throw new \Exception('not_allowed',  self::$ERROR_CODES['FORBIDDEN']);
        }
    }

    public function finishOrderByWorker($params = [])
    {
        $OrderData = (new Order)->find(Arr::get($params, 'order_id'));
        $AppCommission = (new Worker)->getWorkerCommission($OrderData->worker_id);
        $Prices = $this->calculateOrderPrices(Arr::get($params, 'services'), Arr::get($params, 'payment_type'), $AppCommission);
        $Order = $this->updateOrder([
            'order_id'              => Arr::get($params, 'order_id'),
            'status_id'             => $this->statuses['work_finished_by_worker'],
            'price'                 => Arr::get($Prices, 'order_price'),
            'total_price'           => Arr::get($Prices, 'order_price') + Arr::get($Prices, 'bank_commission'),
            'payment_type'          => Arr::get($params, 'payment_type'),
            'bank_percent'          => Arr::get($params, 'payment_type') == $this->paymentTypes['balance'] ? (int) $this->getConfig('bank_percent') : 0,
            'bank_percent_amount'   => Arr::get($Prices, 'bank_commission'),
            'app_percent'           => $AppCommission,
            'app_percent_amount'    => Arr::get($Prices, 'app_commission'),
            'ended_at'              => Carbon::now()->toDateTimeString()
        ]);
        foreach (Arr::get($params, 'services') AS $service)
        {
            (new OrderService)->create([
                'order_id'    => Arr::get($params, 'order_id'),
                'title'       => Arr::get($service, 'title'),
                'amount'      => Arr::get($service, 'amount'),
                'unit_id'     => Arr::get($service, 'unit_id'),
                'unit_price'  => Arr::get($service, 'unit_price'),
            ]);
        }
        $Client = User::find($OrderData->client_id);
        (new Message)->sendSystemMessage($Order->conversation_id, 'contract_is_ready', ['order_status_id' => $this->statuses['work_finished_by_worker']], [], $Client->lang);
        (new Notification)->sendTemplateNotifications($Order->client_id, 'unPaidOrder', [],['type' => 'order', 'id' => Arr::get($params, 'order_id')], 'order_details');
        (new User)->checkUserBusyStatus($Order->worker_id);
        if ($Order->custom_client_phone) {
            $this->SendSMS($Order->custom_client_phone, $this->generateAcceptanceSurrenderMessage($Order->id));
        }
        return $Order->toArray();


    }

    public function finishOrderByUser($params = [])
    {
        $UserType = (new User)->getUserType();
        $Order = self::where($this->getWhereUser(), config('auth.UserID'))->find(Arr::get($params, 'order_id'));
        if (!$Order) {
            throw new \Exception('order not found',  self::$ERROR_CODES['NOT_FOUND']);
        }
        $toStatus = $this->statuses['user_accepted'];
        if ($Order->payment_type == $this->paymentTypes['cash']) {
            $UserType = 'client_pays_cash';
            $toStatus = $this->statuses['user_payed'];
        }
        $this->validateUpdateOrder($UserType, $Order->status_id, $toStatus);
        $Order->update([
            'status_id'     => $toStatus,
            'seen'          => 0
        ]);
        $User = (new User)->find($Order->client_id);

        if ($Order->payment_type == $this->paymentTypes['balance']) {
            return (new Payment)->paymentOrder($Order);
        }
        $AppPercent = (new Worker)->getWorkerCommission($Order->worker_id);
        (new Notification)->sendTemplateNotifications([$Order->worker_id], 'userAcceptedOrder', [$User->name.' '.$User->surname], ['type' => 'order', 'id' => Arr::get($params, 'order_id')] ,'order_details');
        (new Worker)->updateBalance($Order->worker_id, $this->getPriceToCharge($Order->total_price, $AppPercent), false);
        $Client = User::find($Order->client_id);
        (new Message)->sendSystemMessage($Order->conversation_id, 'payed_with_cash', ['order_status_id' => $toStatus], [], $Client->lang);
        (new User)->checkUserBusyStatus($Order->worker_id);
        return $Order->toArray();
    }

    public function userHasOrderUpdates()
    {
        $UserModel = new User();
        $minimumDate = Carbon::now()->subMinutes(2)->toDateTimeString();
        $UserType = $UserModel->getUserType();
        $NewOrder = $UserType == 'worker' ? self::where($this->getWhereUser(), config('auth.UserID'))->where('updated_at', '>', $minimumDate)->where('seen', '=', 0)->limit(1)->first() : 0;
        $NewOffer = (new Offer)->where('updated_at', '>', $minimumDate);
        $NewOffer->where('status_id', (new Offer)->Statuses['active']);
        if ($UserType == 'client') {
            $NewOffer->where('client_id', config('auth.UserID'))->where('seen', '=', 0);
        } else {
            $NewOffer->whereHas('OfferedWorkers', function ($q) {
               $q->where('worker_id', config('auth.UserID'));
               $q->where('seen', '=', 0);
            });
        }
        $NewOffer = $NewOffer->limit(1)->first();

        //has new order or new offer
        return [
            'has_updates' => ($NewOrder || $NewOffer)
        ];
    }

    public function calculateOrderPrices($Services, $PaymentType, $AppCommission)
    {
        $Price = 0;
        foreach ($Services AS $service) {
            $Price += Arr::get($service,'amount', 0) * Arr::get($service, 'unit_price');
        }

        return [
            'order_price'       => $Price,
            'bank_commission'   => $PaymentType == $this->paymentTypes['balance'] ? ($Price / 100 * $this->getConfig('bank_percent')) : 0,
            'app_commission'     => round($Price / 100 * $AppCommission, 2)
        ];
    }

    public function getPriceToCharge($Price, $AppPercent)
    {
        return $Price * $AppPercent/100;
    }

    private function seenOrders()
    {
        $this->where($this->getWhereUser(), config('auth.UserID'))->where('seen', 0)->update(['seen' => 1]);
    }

    public function getOrdersAndOffers($params)
    {
        $Limit = Arr::get($params,'limit', 20); // 10
        $Page = Arr::get($params, 'page', 1); // 2
        $UserType = (new User)->getUserType();
        $OffersCount = (new Offer)->getQuery($UserType)->count(); //10
        $OrdersCount = $this->getQuery($UserType, $params)->count(); //2
        //თუ არ არის ოფერები ან ამ ფეიჯზე აღარ უნდა გამოვიდეს
        if (!$OffersCount ||  $Limit * $Page - $Limit >= $OffersCount) {
            $params['page'] = (($Limit * $Page - $Limit - $OffersCount) / $Limit) + 1;
            return array_merge(['offers' => []], $this->getAll($params, false, $OrdersCount), ['pagination' => $this->GetPageData($OffersCount + $OrdersCount, $Limit, $Page)]);
        }
        // თუ ოფერები მთლიანად ავსებს ფეიჯს
        if ($OffersCount >= $Limit * $Page) {
            return array_merge((new Offer)->getAll($params, false, $OffersCount), ['orders' => []], ['pagination' => $this->GetPageData($OffersCount + $OrdersCount, $Limit, $Page)]);
        }
        // ფეიჯზე უნდა გამოვიდეს ოფერებიც და ორდერებიც
        $params['limit'] = max($Limit  - ($Limit * $Page - $OffersCount), 0);
        $Offers = (new Offer)->getAll($params, false, $OffersCount);
        $params['page'] = 1;
        $params['limit'] = min($Limit * $Page - $OffersCount, $Limit);
        $Orders = $this->getAll($params, false, $OrdersCount);
        return array_merge($Offers, $Orders, ['pagination' => $this->GetPageData($OffersCount + $OrdersCount, $Limit, $Page)]);
    }

    private function getQuery($UserType, $params)
    {
        $Query = $this->where($this->getWhereUser(), config('auth.UserID'));
        $Query->with('MyRate', ($UserType == 'worker' ? 'Client' : 'Worker'));

        if ($StatusID = Arr::get($params, 'status_id')) {
            $Query->where('status_id', $StatusID);
        }
        //TODO config available statuses
        if (Arr::get($params,'status_id') !== null) {
            $Query->where('status_id',Arr::get($params, 'status_id'));
        }
        if ($dateFrom = Arr::get($params, 'date_from')) {
            $Query->where('start_date', '>=', date('Y-m-d', strtotime($dateFrom.' 00:00:01')));
        }
        if ($dateTo = Arr::get($params, 'date_to')) {
            $Query->where('start_date', '<=', date('Y-m-d', strtotime($dateTo.' 23:23:59')));
        }
        if ($keyword = Arr::get($params,'keyword')) {
            $Query->where('title', 'like', '%'.$keyword.'%');
        }

        if ($conversationID = Arr::get($params,'conversation_id')) {
            $Query->where('conversation_id', $conversationID);
        }
        return $Query;
    }

    private function generateAcceptanceSurrenderMessage($OrderID)
    {
        $messageBaseKey = 'system_messages.acceptance_delivery_message_';

        $messageKeys = [
            $messageBaseKey . 'greeting',
            $messageBaseKey . 'price_sum',
            $messageBaseKey . 'currency_unit',
            $messageBaseKey . 'prof_recommend',
        ];

        $translations = [];

        TranslateMessage::whereIn('code', $messageKeys)->get()->map(function ($item) use (&$translations) {
            $translations[$item->code] = $item->getContentAttribute();
        });

        $text = $translations[$messageBaseKey . 'greeting'] . " \n";

        $Order = self::with('OrderServices', 'OrderServices.Unit')->find($OrderID);
        if (!$Order) {
            return;
        }

        foreach ($Order->OrderServices AS $Service) {
            $Service = $Service->toArray();
            $text .= "\n".Arr::get($Service, 'title').' '
               .Arr::get($Service, 'amount')
               .Arr::get($Service, 'unit.title').' - '
               .(Arr::get($Service, 'unit_price')). $translations[$messageBaseKey . 'currency_unit'];
        }
        $text .= "\n\n" . $translations[$messageBaseKey . 'price_sum'] . " ".$Order->total_price.$translations[$messageBaseKey . 'currency_unit'];
        $text .= "\n\n" . $translations[$messageBaseKey . 'prof_recommend'];
        return $text;
    }
}
