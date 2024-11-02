<?php namespace skillset\Notifications\Models;

use Carbon\Carbon;
use Cms\Traits\ApiResponser;
use Cms\Traits\PushNotifications;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Model;
use Pheanstalk\Exception;
use RainLab\Translate\Models\Locale;
use RainLab\User\Models\User;
use RainLab\User\Models\Worker;
use skillset\Configuration\Traits\Config;
use skillset\Conversations\Models\Message;
use skillset\Jobs\Classes\NotifyUsersNewJobAdded;
use skillset\Jobs\Models\Job;
use skillset\Marketplace\Models\Application;
use skillset\Offers\Models\Offer;
use skillset\Offers\Models\OfferWorker;
use skillset\Orders\Models\Order;
use skillset\Jobs\Models\Order as JobOrder;
use skillset\Services\Models\Service;

/**
 * Model
 */
class Notification extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use PushNotifications;
    use Config;
    use ApiResponser;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'skillset_notifications_';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $belongsTo = [
        'Template'    => [NotificationTemplate::class, 'key' => 'template_id', 'otherKey' => 'id'],
    ];

    // id => after hours
    public $frequencies = [
        1 => 1,
        2 => 3,
        3 => 24,
        4 => 72,
        5 => 168,
        6 => 720
    ];

    // id => userType
    public $sendTo = [
        1   => 1,
        2   => 0
    ];

    public $templates = [
        'unActiveOffer'         => 1,
        'newOffer'              => 2,
        'fillBalance'           => 6,
        'workerStartedChat'     => 5,
        'offerAcceptedByWorker' => 3,
        'offerRejectedByWorker' => 7,
        'offerAcceptedByClient' => 8,
        'offerRejectedByClient' => 9,
        'unPaidOrder'           => 10,
        'userAcceptedOrder'     => 11,
        'userPaidOrder'         => 12,
        'unratedWorker'         => 14,
        'unratedClient'         => 15,
        'newMessage'            => 17,
        'endDateNotification'   => 18,
        'userActivated'         => 21,
        'userDeleted'           => 22,
        'newJob'                => 100,
        'newMarketplaceApplication'         => 101,
        'marketplacePreInvoiceSent'         => 102,
        'marketplacePreInvoiceAccepted'     => 103,
        'marketplacePreInvoiceRejected'     => 104,
        'marketplaceInvoiceSent'            => 105, //acceptance - delivery
        'marketplaceInvoiceAccepted'        => 106, //acceptance - delivery,
        'forumNewComment'                   => 107,
    ];

    public function sendAutoNotifications()
    {
        $NotificationsToSend = self::with('Template', 'Template.translations')->where('status_id', 1)->get();
        foreach ($NotificationsToSend AS $Notification) {
            if (!Arr::get($Notification, 'frequency')) {
                $this->sendInstantNotification($Notification);
                continue;
            }
            $this->sendFrequentNotification($Notification);
        }
        return true;
    }

    private function sendInstantNotification($Notification)
    {
        $Now = Carbon::now();
        if ($Now->hour >= $this->getConfig('silence_hours_from') OR $Now->hour <= $this->getConfig('silence_hours_to')) {
            return false;
        }
        $params = $Notification->toArray();
        $template = $this->remapMultiLangTemplate(Arr::get($params, 'template'));
        $this->sendNotifications(Arr::get($params, 'send_to'), Arr::get($template, 'title'), Arr::get($template, 'description'));
        $Notification->update(['status_id' => 0, 'last_send_date' => $Now->toDateTimeString()]);
    }

    private function sendFrequentNotification($Notification)
    {
        $params = $Notification->toArray();
        $frequency = Arr::get($this->frequencies, Arr::get($params, 'frequency', 0), 0);
        $Now = Carbon::now();

        if ($LastSendDate = Arr::get($params, 'last_send_date')) {
            $LastSendDate = Carbon::parse($LastSendDate);
            $HoursPassed = $Now->diffInHours($LastSendDate);
            if ($HoursPassed < $frequency) {
                return false;
            }
        }
        if ($frequency >= 24 && $Now->hour != $this->getConfig('frequency_messages_time')) {
            return false;
        }

        $Notification->update(['last_send_date' => $Now->toDateTimeString()]);
        $template = $this->remapMultiLangTemplate(Arr::get($params, 'template'));
        return $this->sendNotifications(Arr::get($params, 'send_to'), Arr::get($template, 'title'), Arr::get($template, 'description'));
    }

    public function sendNotifications($SendTo, $Title, $Description)
    {
        $UserModel = (new User);
        if ($SendTo AND Arr::get($this->sendTo, $SendTo) !== null) {
            $UserModel = $UserModel->where('user_type', Arr::get($this->sendTo, $SendTo));
        }
        $UserIDs = $UserModel->pluck('id')->toArray();
        if (!$UserIDs) {
            return false;
        }
        foreach (array_chunk($UserIDs, 1000) as $UserID) {
            $this->SendPushNotification($UserID, $Title, $Description);
        }
        return true;

    }

    private function generateMessage($Message, $params)
    {
        if (Arr::get($params, 'service_id')) {
            $service = (new Service)->find(Arr::get($params, 'service_id'));
            $params[] = Arr::get($service, 'title');
            unset($params['service_id']);
        }
        if (substr_count($Message, '%s') == count($params)) {
            return vsprintf($Message, $params);
        }
        return str_replace('%s', '', $Message);
    }

    public function sendUnActiveOfferNotification($UserID, $ServiceID, $SearchParams, $OfferID)
    {
        $Template = $this->getTemplateMultiLang('unActiveOffer');
        $Messages = [];
        foreach ($Template['description'] AS $key =>  $description) {
            $Messages[$key] = $this->generateMessage($description, ['service_id' => $ServiceID]);
        }

        $SearchParams = array_merge((is_array($SearchParams) ? $SearchParams : []), ['sort' => 3, 'service_id' => $ServiceID]);
        $this->SendPushNotification($UserID, Arr::get($Template, 'title'), $Messages, Arr::get($Template, 'icon_type'),
            Arr::get($Template, 'button_title'), 'search', $SearchParams, true);
    }

    private function getTemplate($name) {
        return (new NotificationTemplate)->find(Arr::get($this->templates, $name))->toArray();
    }
    private function getTemplateMultiLang($name) {
        $template = (new NotificationTemplate)->with('translations')->find(Arr::get($this->templates, $name))->toArray();
        return $this->remapMultiLangTemplate($template);
    }

//    private function getDeviceToken($UserID) {
//        $User = (new User)->find($UserID);
//        if (!$User) {
//            return;
//        }
//        return $User->device_token;
//    }

//    public function seenNotification(array $params = [])
//    {
//        $item = self::findOrFail(Arr::get($params, 'notification_id'));
//        $item->seen = 1;
//        $item->save();
//        return true;
//
//    }
    public function notifyWorkersWithNegativeBalance()
    {
        $minimumDate = $this->getsubDate($this->getConfig('notify_users_with_negative_balance_in'));
        $UserIDs = (new Worker)->where('balance', '<', 0)->where('last_notification_at', '<', $minimumDate)->pluck('id')->toArray();
        $this->sendTemplateNotifications($UserIDs, 'fillBalance', [],['balance_popup' => true],'profile');
    }

    public function notifyClientWithUnPayedFinishedWork()
    {
        $OrderObj = new Order();
        $Orders = $OrderObj->whereIn('status_id', [$OrderObj->statuses['work_finished_by_worker'], $OrderObj->statuses['user_accepted']])
            ->whereHas('Client', function($q){
                $q->where('last_notification_at', '<', $this->getsubDate($this->getConfig('notify_client_for_finished_work_in')));
            })
        ->get();
        foreach ($Orders AS $Order) {
            $this->sendTemplateNotifications($Order->client_id, 'unPaidOrder', [],['type' => 'order', 'id' => $Order->id], 'order_details');
        }
    }

    /**
     * @throws GuzzleException
     * @throws \Google\Exception
     */
    public function sendTemplateNotifications($UserIDs, $TemplateName, $params = [], $actionParams = [], $actionPage = '', $topic = null)
    {
        if (!$UserIDs) {
            return;
        }
        $UserIDs = is_array($UserIDs) ? $UserIDs : [$UserIDs];
        if (empty($UserIDs)) {
            return;
        }
        $Now = Carbon::now();
        if ($Now->hour >= $this->getConfig('silence_hours_from') OR $Now->hour <= $this->getConfig('silence_hours_to')) {
            return;
        }
        $Template = $this->getTemplateMultiLang($TemplateName);
        $Message = Arr::get($Template, 'description');
        if ($params) {
            $Message = $this->generateMessage($Message, $params);
        }

        $this->SendPushNotification($UserIDs, Arr::get($Template, 'title'), $Message, Arr::get($Template, 'icon_type'), Arr::get($Template, 'button_title'), $actionPage, $actionParams, false, $topic, Arr::get($Template, 'id'));
    }

//    public function sendTemplateNotificationsByUserIDs($UserIDs, $TemplateName, $params = [])
//    {
//        if (!$UserIDs || empty($UserIDs)) {
//            return;
//        }
//        $UserIDs = is_array($UserIDs) ? $UserIDs : [$UserIDs];
//        $DeviceTokens = (new User)->whereIn('id', $UserIDs)->pluck('device_token')->toArray();
//        $this->sendTemplateNotifications($DeviceTokens, $TemplateName, $params);
//    }

    public function notifyUnratedOrdersUsers()
    {
        $OrderModel = (new Order);
        $OrderModel->with('Rates', 'Client', 'Worker')
            ->where('status_id', $OrderModel->statuses['user_payed'])
            ->where('rated', '!=', 1)
            ->get()->map(function($item) {
                if (count($item->Rates) == 2) {
                    return;
                }
                if (!$item->Rates OR count($item->Rates) == 0) {
                    $this->NotifyUserToRate($item->Client, $item->Worker, $item->id);
                    $this->NotifyUserToRate($item->Worker, $item->Client, $item->id);
                }
                foreach ($item->Rates AS $rate) {
                    //თუ კლიენტმა არ შეაფასა შეკვეთა
                    if ($item->client_id != $rate->rater_id) {
                        $this->NotifyUserToRate($item->Client, $item->Worker, $item->id);
                        return;
                    }
                    //თუ ხელოსანმა არ შეაფასა შეკვეთა
                    $this->NotifyUserToRate($item->Worker, $item->Client, $item->id);
                    return;
                }
            });
    }

    public function notifyUsersAboutNewChat()
    {
        $OfferModel = (new OfferWorker);
        $now = Carbon::now()->toDateTimeString();
        $OfferedCleints = $OfferModel
            ->with('Offer', 'Details')
            ->where('status_id', $OfferModel->statuses['chat_created'])
            ->where('notification_count', '<', $this->getConfig('notify_about_new_chat_max_times'))
            ->where('seen', '=', 0)
            ->where(function($q) {
                $q->orWhere('notification_count', 0);
                $q->orWhere('last_notified_at', '<=', $this->getsubDate($this->getConfig('notify_about_new_chat_in')));
            })->get()->map(function ($Offer) use ($now){
                $Offer->notification_count = ++$Offer->notification_count;
                $Offer->last_notified_at = $now;
                $Offer->save();
                $this->sendTemplateNotifications($Offer->Offer->client_id, 'workerStartedChat', [$Offer->Details->name.' '.$Offer->Details->surname]);
            })->toArray();


    }

    public function notifyUsersAboutUnreadMessages()
    {
        $alreadySent = [];
        $MessagesObj = (new Message)->with('ConversationUsers', 'Sender')
            ->where('seen', 0)
            ->where('notified', 0);
        $MessagesObj->get()->map(function($item) use (&$alreadySent){
           foreach ($item->ConversationUsers AS $User) {
               if ($User->user_id != $item->user_id AND !in_array($item->user_id, $alreadySent)) {
                   $alreadySent[] = $item->user_id;
                   $this->sendTemplateNotifications($User->user_id, 'newMessage', [$item->Sender->name.' '.$item->Sender->surname]);
               }
           }
        });
        $MessagesObj->update(['notified' => 1]);
    }

    private function NotifyUserToRate($User, $UserToRate, $OrderID)
    {
        if (!$User || !$UserToRate) {
            return;
        }
        $HoursPassed = Carbon::now()->diffInHours(Carbon::parse($User->last_notification_at));
        if ($HoursPassed <= $this->getConfig('notify_user_with_unrated_order')) {
            return;
        }
        $this->sendTemplateNotifications($User->id, ($User->user_type == 1 ? 'unratedClient' : 'unratedWorker'), [$UserToRate->name.' '.$UserToRate->surname], ['type' => 'order', 'id' => $OrderID], 'order_details');

    }

    public function notifyUnratedJobOrderUsers()
    {
        $order = (new JobOrder);

        $order->with('rates', 'offer.job')
            ->where('status', $order->statuses['paid'])
            ->where('rated', '!=', 1)
            ->get()->map(function($item) use ($order) {
                if ($item->rates && count($item->rates) == 2) {
                    return;
                }

                $client = $order->getUserByRole($item, 'client');
                $worker = $order->getUserByRole($item, 'worker');

                if (!$item->rates OR count($item->rates) == 0) {
                    $this->notifyJobUserToRate($client, $worker, 'client', $item);
                    $this->notifyJobUserToRate($worker, $client, 'worker', $item);
                }
                foreach ($item->rates AS $rate) {
                    //თუ კლიენტმა არ შეაფასა შეკვეთა
                    if ($client->id != $rate->rater_id) {
                        $this->notifyJobUserToRate($client, $worker, 'client', $item);
                        return;
                    }
                    //თუ ხელოსანმა არ შეაფასა შეკვეთა
                    $this->notifyJobUserToRate($worker, $client, 'worker', $item);
                    return;
                }
            });
    }
    private function notifyJobUserToRate($user, $userToRate, $userType, $order)
    {
        if (!$user || !$userToRate) {
            return;
        }

        $hoursPassed = Carbon::now()->diffInHours(Carbon::parse($user->last_notification_at));

        if ($hoursPassed <= $this->getConfig('notify_user_with_unrated_order')) {
            return;
        }

        $this->sendTemplateNotifications(
            $user->id,
            ($userType == 'client' ? 'unratedClient' : 'unratedWorker'),
            [$userToRate->name.' '.$userToRate->surname],
            ['type' => 'job_order', 'id' => $order->id, 'show_rating_popup' => true],
            'job_order_details'
        );
    }

    private function getsubDate($subHours)
    {
        return Carbon::now()->subHours($subHours)->toDateTimeString();
    }

    public function notifyWorkersAboutEndDate()
    {
        $Now = Carbon::now();
        if ($Now->hour >= $this->getConfig('silence_hours_from') OR $Now->hour <= $this->getConfig('silence_hours_to')) {
            return;
        }
        $OrderModel = (new Order);
        $OrderModel->where('status_id', $OrderModel->statuses['work_started'])
            ->where('end_date', $Now->toDateString())
            ->where('end_date_notified', '<', '2')
            ->get()->each(function($item) use ($Now){
                if ($item->end_date_notified == 0 || $Now->hour == 15) {
                    $this->sendTemplateNotifications($item->worker_id, 'endDateNotification', [$item->title], ['type' => 'order', 'id' => $item->id], 'order_details');
                    $item->update(['end_date_notified' => $item->end_date_notified + 1]);
                }

            });

        $jobOrderModel = (new JobOrder());
        $jobOrderModel->with('offer.job')
            ->where('status', $OrderModel->statuses['work_started'])
            ->where('estimated_completed_date', $Now->toDateString())
            ->where('end_date_notified', 0)
            ->get()->each(function($item) use ($Now){
                if ($Now->hour == 15) {

                    $worker = $item->getUserByRole($item, 'worker');

                    $this->sendTemplateNotifications(
                        $worker->id,
                        'endDateNotification',
                        [$item->offer->job->title],
                        ['type' => 'job_order', 'id' => $item->id],
                        'job_order_details'
                    );

                    $item->update(['end_date_notified' => 1]);
                }

            });


    }

    public function sendTestNotification(array $params)
    {
        $this->sendUnActiveOfferNotification(Arr::get($params, 'user_id'), Arr::get($params, 'service_id'), $params, 0);
    }

    public function notifyUsersAboutNewJobAdded($id)
    {
        Queue::push('skillset\jobs\classes\NotifyUsersNewJobAdded', ['id' => $id]);
    }

    public function notifyUsersAboutNewMarketplaceAppAdded($id)
    {
        Queue::push('skillset\marketplace\classes\NotifyUsersNewMarketplaceAppAdded', ['id' => $id]);
    }

    private function remapMultiLangTemplate($template)
    {
        $defaultLang = Locale::getDefault()->code;
        $Return = $template;

        $Return['title'] = [$defaultLang => Arr::get($template, 'title')];
        $Return['description'] = [$defaultLang => Arr::get($template, 'description')];
        $Return['button_title'] = [$defaultLang => Arr::get($template, 'button_title')];
        foreach (Locale::listEnabled() AS $key => $lang) {
            $Return['title'][$key] = Arr::get($template, 'title');
            $Return['description'][$key] = Arr::get($template, 'description');
            $Return['button_title'][$key] = Arr::get($template, 'button_title');
        }
        foreach (Arr::get($template, 'translations') AS $translation) {
            $data = json_decode(Arr::get($translation, 'attribute_data'), true);
            $Return['title'][Arr::get($translation,'locale')] = trim(Arr::get($data, 'title')) ?: Arr::get($template, 'title');
            $Return['description'][Arr::get($translation,'locale')] = trim(Arr::get($data, 'description')) ?: Arr::get($template, 'description');
            $Return['button_title'][Arr::get($translation,'locale')] = trim(Arr::get($data, 'button_title')) ?: Arr::get($template, 'button_title');
        }
        return $Return;
    }


    /**
     * @throws \Google\Exception
     */

}
