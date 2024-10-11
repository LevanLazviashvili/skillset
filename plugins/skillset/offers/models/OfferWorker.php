<?php namespace skillset\Offers\Models;

use Carbon\Carbon;
use Cms\Traits\ApiResponser;
use Cms\Traits\SmsOffice;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Model;
use RainLab\Translate\Models\Message as TranslateMessage;
use RainLab\User\Models\User;
use RainLab\User\Models\Worker;
use skillset\Configuration\Traits\Config;
use skillset\Conversations\Models\Conversation;
use skillset\Conversations\Models\Message;
use skillset\Notifications\Models\Notification;
use skillset\Orders\Models\Order;
use skillset\Orders\Models\OrderService;
use skillset\Orders\Models\OrderServiceTmp;

/**
 * Model
 */
class OfferWorker extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use Config;
    use ApiResponser;
    use SmsOffice;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'skillset_offers_workers';
    public $timestamps = true;
    protected $visible = ['status_id', 'worker_response', 'Details', 'conversation_id', 'offer_id', 'end_date', 'id'];
    public $statuses = [
        'offer_created'                 => 0,
        'chat_created'                  => 1,
        'offer_accepted_by_worker'      => 2,
        'offer_rejected_by_worker'      => -1,
        'offer_rejected_by_client'      => -2,
        'offer_accepted_by_client'      => 3
    ];
    private $availableStatuses = [
        'worker'    => [0 => [1,-1], 1 => [-1, 2], 2 => [-1]],
        'client'    => [2 => [3,-2]]
    ];


    public function Details()
    {
        return $this->hasOne(User::class, 'id', 'worker_id');
    }

    public function Offer()
    {
        return $this->hasOne(Offer::class, 'id', 'offer_id');
    }

    public function Worker()
    {
        return $this->hasOne(User::class, 'id', 'worker_id');
    }
    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public function store($params = [])
    {
        $Data = [];
        $OfferID = Arr::get($params, 'offer_id');
        $Now = \Carbon\Carbon::now();
        $Workers = is_array(Arr::get($params, 'workers')) ? Arr::get($params, 'workers') : explode('.', Arr::get($params, 'workers'));
        foreach ($Workers AS $workerID) {
            $Data[] = [
                'offer_id'      => $OfferID,
                'worker_id'     => $workerID,
                'status_id'     => 0,
                'created_at'    => $Now,
                'updated_at'    => $Now
            ];
        }
        self::insert($Data);
        (new Notification)->sendTemplateNotifications($Workers, 'newOffer', [],['type' => 'offer', 'id' => $OfferID] ,'order_details');
    }

    public function updateOffer($params = [], $ByAdmin = false)
    {
        $UserType = $ByAdmin ? 0 : (new User)->getUserType();
        $WorkerOffer = $this->getWorkerOffer(Arr::get($params, 'offer_id'), $UserType, Arr::get($params, 'worker_id'), $ByAdmin);
        if (!$ByAdmin) {
            $this->validateUpdateOffer($UserType, $WorkerOffer->Offer->status_id, $WorkerOffer->status_id, Arr::get($params, 'status_id'), $WorkerOffer->Worker);
        }
        $updateParams = [
            'status_id'     => Arr::get($params, 'status_id')
        ];
        if (Arr::get($params, 'status_id') == $this->statuses['offer_accepted_by_worker']) {
            $updateParams['end_date'] = Arr::get($params, 'end_date');
        }

        $WorkerOffer->update($updateParams);

        $params['workerOfferObj'] = $WorkerOffer;

        $this->activateStatusChangeTriggers($params, $ByAdmin);

        return $params['workerOfferObj']->toArray();
    }

    private function activateStatusChangeTriggers($params, $ByAdmin = false)
    {
        $User = (new User)->find($ByAdmin ? 0 : config('auth.UserID'));
        $params['workerOfferObj']->Offer->update(['seen' => 0]);
        $SysMessageParams = [];

        switch (Arr::get($params, 'status_id')) {
            case $this->statuses['chat_created']:
                (new User)->checkUserBusyStatus($params['workerOfferObj']->worker_id);
                $this->startConversation(Arr::get($params, 'workerOfferObj'));
                $this->limitOfferAcceptedWorkers(Arr::get($params, 'offer_id'));
                break;
            case $this->statuses['offer_accepted_by_worker']:
                (new User)->checkUserBusyStatus($params['workerOfferObj']->worker_id);
                (new Notification)->sendTemplateNotifications([$params['workerOfferObj']->Offer->client_id], 'offerAcceptedByWorker', [$User->name.' '.$User->surname],['type' => 'offer', 'id' => Arr::get($params, 'offer_id')] ,'order_details');
                $this->updateOfferEndDate($params['workerOfferObj']->id, Arr::get($params, 'end_date'));
                $params['send_rules'] = true;
                $this->saveOfferServices($params);
                $SysMessageParams[] = Carbon::parse(Arr::get($params, 'end_date'))->format('d-m-Y');
                break;
            case $this->statuses['offer_rejected_by_worker']:
                (new User)->checkUserBusyStatus($params['workerOfferObj']->worker_id);
                $this->checkActiveOffersCount(Arr::get($params, 'offer_id'));
                (new Notification)->sendTemplateNotifications([$params['workerOfferObj']->Offer->client_id], 'offerRejectedByWorker', [$User->name.' '.$User->surname], ['type' => 'offer', 'id' => Arr::get($params, 'offer_id')] ,'order_details');
                break;
            case $this->statuses['offer_accepted_by_client']:
                (new User)->checkUserBusyStatus($params['workerOfferObj']->worker_id);
                $Order = $this->transferOfferToOrder(Arr::get($params, 'workerOfferObj'), $ByAdmin, Arr::get($params, 'custom_client_phone', ''), Arr::get($params, 'custom_client_address', ''));
                (new Notification)->sendTemplateNotifications([$params['workerOfferObj']->worker_id], 'offerAcceptedByClient', [($User->name ?? 'Administration').' '.($User->surname ?? '')], ['type' => 'order', 'id' => Arr::get($Order, 'order_id')] ,'order_details');
                break;
            case $this->statuses['offer_rejected_by_client']:
                (new User)->checkUserBusyStatus($params['workerOfferObj']->worker_id);
                $this->checkActiveOffersCount(Arr::get($params, 'offer_id'));
                (new Notification)->sendTemplateNotifications([$params['workerOfferObj']->worker_id], 'offerRejectedByClient', [$User->name.' '.$User->surname], ['type' => 'offer', 'id' => Arr::get($params, 'offer_id')] ,'order_details');
        }

        (new Message)->sendSystemMessage($params['workerOfferObj']->conversation_id, Arr::get(array_flip($this->statuses), Arr::get($params, 'status_id')), ['offer_status_id' => Arr::get($params, 'status_id')], $SysMessageParams);
    }

    private function getWorkerOffer($OfferID, $UserType, $WorkerID = null, $ByAdmin = false)
    {
        $OfferQuery = self::with('Offer', 'Worker')
            ->where('offer_id', $OfferID)
            ->where('status_id', '!=', $this->statuses['offer_accepted_by_client']);
        if ($UserType == 'client'){
            if (!$ByAdmin) {
                $OfferQuery->whereHas('Offer', function ($q) {
                    $q->where('client_id', config('auth.UserID'));
                });
            }
            $OfferQuery->where('worker_id', $WorkerID);
        } else {
            $OfferQuery->where('worker_id',config('auth.UserID'));
        }
        return $OfferQuery->firstOrFail();
    }

    private function validateUpdateOffer($UserType, $OfferStatus, $StatusFromID, $StatusToID, $Worker)
    {
        if (!$Worker || $Worker->status_id <= 0)
        {
            throw new \Exception('worker_is_busy', self::$ERROR_CODES['FORBIDDEN']);
        }
        $OfferObj = (new Offer);
        if ($OfferStatus == $OfferObj->Statuses['finished']) {
            throw new \Exception('offer_finished', self::$ERROR_CODES['FORBIDDEN']);
        }
        if ($OfferStatus == $OfferObj->Statuses['canceled']) {
            throw new \Exception('offer_canceled', self::$ERROR_CODES['FORBIDDEN']);
        }
        if (in_array($StatusToID, [$this->statuses['chat_created'], $this->statuses['offer_accepted_by_worker']])) {
            $User = (new User)->find(config('auth.UserID'));
//            if ($User->balance < 0) {
//                throw new \Exception('could not accept offer with negative balance', self::$ERROR_CODES['FORBIDDEN']);
//            }
        }

        $availableStatuses = Arr::get($this->availableStatuses, $UserType.'.'.$StatusFromID, []);
        if (!$availableStatuses) {
            throw new \Exception('not_allowed', self::$ERROR_CODES['FORBIDDEN']);
        }
        if (!in_array($StatusToID, $availableStatuses)) {
            throw new \Exception('not_available_status', self::$ERROR_CODES['FORBIDDEN']);
        }
    }

    private function limitOfferAcceptedWorkers($OfferID)
    {
        $allowedCount = $this->getConfig('max_offer_agrees');
        $Count = self::where('offer_id', $OfferID)->where('status_id', $this->statuses['chat_created'])->count();
        if ($Count >= $allowedCount) {
            self::where('offer_id', $OfferID)->where('status_id', $this->statuses['offer_created'])->update(['status_id' => $this->statuses['offer_rejected_by_client']]);
        }
    }

    private function checkActiveOffersCount($OfferID)
    {
        $activeOffersCount = self::where('offer_id', $OfferID)->whereIn('status_id', [$this->statuses['offer_created'], $this->statuses['chat_created'], $this->statuses['offer_accepted_by_worker'], $this->statuses['offer_accepted_by_client']])->count();
        if ($activeOffersCount == 0) {
            $this->sendNotficationToClient($OfferID);
            (new Offer)->cancelOffer($OfferID);
        }
    }

    private function sendNotficationToClient($OfferID)
    {
        $Offer = (new Offer)->find($OfferID);
        if (!$Offer) {
            return;
        }
        (new Notification)->sendUnActiveOfferNotification($Offer->client_id, $Offer->service_id, json_decode($Offer->search_params, 1), $OfferID);
    }

    public function startConversation($WorkerOffer)
    {
        $ConversationID = (new Conversation)->startNewConversation([$WorkerOffer->worker_id, $WorkerOffer->Offer->client_id], $WorkerOffer->worker_id);
        $WorkerOffer->update(['conversation_id' => $ConversationID]);
        return $ConversationID;
    }

    private function transferOfferToOrder($WorkerOffer, $ByAdmin = false, $CustomClientPhone = '', $CustomClientAddress = '')
    {
        $WorkerOffer->Offer()->update(['status_id' => (new Offer)->Statuses['finished']]);
        $OrderModel = (new Order);
        $Order = $OrderModel->createOrder([
            'client_id'             => $WorkerOffer->Offer->client_id,
            'worker_id'             => $WorkerOffer->worker_id,
            'title'                 => $WorkerOffer->Offer->title ?? '',
            'service_id'            => $WorkerOffer->Offer->service_id ?? '',
            'description'           => $WorkerOffer->Offer->offer,
            'status_id'             => $OrderModel->statuses['work_started'],
            'conversation_id'       => $WorkerOffer->conversation_id,
            'end_date'              => $WorkerOffer->end_date,
            'custom_client_phone'   => $CustomClientPhone,
            'custom_client_address' => $CustomClientAddress,
            'comment'               => $WorkerOffer->Offer->comment
        ], $ByAdmin);
        $this->SetOrderIDToOfferedServices($WorkerOffer->offer_id, Arr::get($Order, 'order_id'));
        return $Order;
    }

    private function updateOfferEndDate($workerOfferID, $endDate)
    {
        self::find($workerOfferID)->update(['end_date' => $endDate]);
    }

    private function saveOfferServices($params)
    {

        (new OrderServiceTmp)->where('offer_id', Arr::get($params, 'offer_id'))->delete();
        $Offer = (new Offer)->with('Client')->find(Arr::get($params, 'offer_id'));
        foreach (Arr::get($params, 'services', []) AS $service)
        {
            (new OrderServiceTmp)->create([
                'offer_id'    => Arr::get($params, 'offer_id'),
                'title'       => Arr::get($service, 'title'),
                'amount'      => Arr::get($service, 'amount'),
                'unit_id'     => Arr::get($service, 'unit_id'),
                'unit_price'  => Arr::get($service, 'unit_price'),
            ]);
        }

        $systemMessage = $this->GenerateOrderServicesMsg(Arr::get($params, 'offer_id'), Arr::get($params, 'end_date'));
        if (Arr::get($params, 'send_rules')) {
            (new Message)->sendSystemMessage(Arr::get($params, 'workerOfferObj')->conversation_id, 'offered_services_pretext');
        }
        if ($systemMessage) {
            (new Message)->sendSystemMessage(Arr::get($params, 'workerOfferObj')->conversation_id, 'offered_services', [], [$systemMessage]);
            $UserPhone = $Offer->custom_client_phone ? $Offer->custom_client_phone :Arr::get($Offer,'Client.username');
            $this->SendSMS($UserPhone, vsprintf((new Message)->getMessageText('offered_services'), [$systemMessage]));


        }
    }

    public function GenerateOrderServicesMsg($OfferID, $endDate)
    {
        $messageBaseKey = 'system_messages.invoice_';

        $messageKeys = [
            $messageBaseKey . 'price_sum',
            $messageBaseKey . 'currency_unit',
            $messageBaseKey . 'estimated_completed_date',
        ];

        $translations = [];

        TranslateMessage::whereIn('code', $messageKeys)->get()->map(function ($item) use (&$translations) {
            $translations[$item->code] = $item->getContentAttribute();
        });

        $OfferedServices = (new OrderServiceTmp)->where('Offer_id', $OfferID)->with('Unit')->get()->toArray();
        if (empty($OfferedServices)) {
            return '';
        }
        $String = '';
        $TotalAmount = 0;
        $count = 1;
        foreach ($OfferedServices AS $key =>  $Service) {
            $Amount =Arr::get($Service, 'amount') * Arr::get($Service, 'unit_price');
            $TotalAmount += $Amount;
            $String .= ($count++).') '.Arr::get($Service, 'title').': '.Arr::get($Service, 'amount').' '.Arr::get($Service, 'unit.title')
                .' - '. number_format($Amount, 2) . ' '
                . $translations[$messageBaseKey . 'currency_unit']
                . ' ('.Arr::get($Service, 'unit_price').' '
                . $translations[$messageBaseKey . 'currency_unit']
                . ' '.Arr::get($Service, 'unit.title').') \n ';
        }
        $String .= $translations[$messageBaseKey . 'price_sum'] .' '.number_format($TotalAmount, 2).' '
            .$translations[$messageBaseKey . 'currency_unit']
            . ' \n  '
            . $translations[$messageBaseKey . 'estimated_completed_date']
            . ' '.$endDate.' ';
        return $String;

    }

    public function editOfferServices(array $params = [])
    {
        $offer = self::where('offer_id', Arr::get($params, 'offer_id'))->where('worker_id', config('auth.UserID'))->where('status_id', $this->statuses['offer_accepted_by_worker'])->first();
        if (!$offer) {
            throw new \Exception('not_allowed', self::$ERROR_CODES['FORBIDDEN']);
        }
        if (Arr::get($params, 'end_date')) {
            self::where('offer_id', Arr::get($params, 'offer_id'))->update(['end_date' => Arr::get($params, 'end_date')]);
        }
        $params['workerOfferObj'] = $offer;
        $this->saveOfferServices($params);
        return [];

    }

    private function SetOrderIDToOfferedServices($offerID, $orderID)
    {
        (new OrderServiceTmp)->where('offer_id', $offerID)->update(['order_id' => $orderID]);
    }


}
