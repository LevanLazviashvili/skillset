<?php namespace skillset\Jobs\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Carbon\Carbon;
use Cms\Traits\ApiResponser;
use Cms\Traits\SmsOffice;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use RainLab\Translate\Classes\Translator;
use RainLab\Translate\Models\Locale;
use RainLab\Translate\Models\Message as TranslateMessage;
use RainLab\User\Models\User;
use RainLab\User\Models\Worker;
use skillset\Configuration\Traits\Config;
use skillset\Conversations\Models\Message;
use skillset\Jobs\Models\Job;
use skillset\Jobs\Models\Offer;
use skillset\Jobs\Models\Order;
use skillset\Jobs\Models\Service;
use skillset\Notifications\Models\Notification;
use skillset\Payments\Models\Payment;

class Offers extends Controller
{
    use ApiResponser;
    use SmsOffice;
    use Config;

    public $implement = [        'Backend\Behaviors\ListController'    ];
    
    public $listConfig = 'config_list.yaml';

    public $requiredPermissions = [
        'jobs' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('skillset.Jobs', 'main-menu-item', 'side-menu-item2');
    }

    public function services(Request $request)
    {
        $authUserId = config('auth.UserID');

        $rules = $this->invoiceRules();

        $data = $request->only(array_keys($rules));

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return $this->errorResponse(
                $validator->getMessageBag(),
                self::$ERROR_CODES['VALIDATION_ERROR'],
                $validator->getMessageBag()
            );
        }

        $validatedData = $request->validate($rules);

        $offer = Offer::find($validatedData['offer_id']);


        $offer->load('job.user');

        if (
            $this->getAuthUserType($offer, $authUserId) != 'worker' ||
            !in_array($offer->status, [$offer->statuses['offer_created'], $offer->statuses['offer_accepted_by_worker']])
        ){
            return $this->errorResponse('Forbidden', 403);
        }

        $oldStatus = $offer->status;

        $offer->update([
            'payment_type' => $validatedData['payment_type'],
            'estimated_completed_date' => $validatedData['estimated_completed_date'],
            'status' => $offer->statuses['offer_accepted_by_worker']
        ]);

        $this->saveServices($offer, $validatedData['services']);

        $client = $this->getUserByRole($offer, 'client');
        $worker = $this->getUserByRole($offer, 'worker');

        if ($oldStatus == $offer->statuses['offer_created']) {
            (new Message)->sendSystemMessage($offer->conversation_id, 'job_offered_services_pretext', [], [], $client->lang);
        }

        $systemMessage = $this->generateOrderServicesMsg($offer->id, $offer->estimated_completed_date, $client->lang);

        (new Message)->sendSystemMessage($offer->conversation_id, 'offered_services', [], [$systemMessage],  $client->lang);
        $sysMessageParams[] = Carbon::parse($validatedData['estimated_completed_date'])->format('d-m-Y');

        (new Message)->sendSystemMessage(
            $offer->conversation_id,
            'offer_accepted_by_worker',
            ['offer_status_id' => $offer->status],
            $sysMessageParams,
            $client->lang
        );



        (new Notification)->sendTemplateNotifications(
            [$client->id],
            'offerAcceptedByWorker',
            [$worker->name.' '.$worker->surname],
            ['type' => 'job_offer', 'id' => $offer->id, 'conversation_id' => $offer->conversation_id],
            'chat'
        );

        $this->SendSMS($client->username, vsprintf((new Message)->getMessageText('offered_services'), [$systemMessage]));

        return $this->successResponse([]);
    }

    public function saveServices($offer, $servicesData, $pre = true)
    {
        $offer->services()->where('pre', $pre)->delete();

        $servicesDataMapped = collect($servicesData)->map(function ($item) use ($pre) {
            $item['pre'] = $pre;

            return $item;
        })->toArray();

        $offer->services()->createMany($servicesDataMapped);
    }

    public function getServices($lang, $id)
    {
        $authUserId = config('auth.UserID');

        $offer = Offer::where(function ($query) use ($authUserId) {
            $query->where('author_id', $authUserId)
                ->orWhereHas('job', function ($query) use ($authUserId) {
                    $query->where('user_id', $authUserId);
                });
        })->with([
            'job',
            'services' => function ($query) {
                $query->with('unit')->where('pre', 1);
            }
        ])
            ->where('id', $id)
            ->first();


        if(!$offer){
            return $this->errorResponse('Not Found', self::$ERROR_CODES['NOT_FOUND']);
        }

        return $this->response([
            'services' => $offer->services
        ]);
    }

    private function invoiceRules()
    {
        Return [
            'offer_id' => 'required|integer|exists:skillset_jobs_offers,id',
            'payment_type' => 'required|integer|in:0,1',
            'estimated_completed_date' => 'required|date|date_format:Y-m-d|after:yesterday',
            'services' => 'required|array',
            'services.*.title' => 'required|string',
            'services.*.amount' => 'required|numeric|min:0',
            'services.*.unit_id' => 'required|integer|exists:skillset_details_units,id',
            'services.*.unit_price' => 'required|numeric',
        ];
    }

    public function generateOrderServicesMsg($offerId, $endDate, $MandatoryLang = null)
    {

        $messageBaseKey = 'system_messages.invoice_';

        $messageKeys = [
            $messageBaseKey . 'price_sum',
            $messageBaseKey . 'currency_unit',
            $messageBaseKey . 'estimated_completed_date',
        ];

        $translations = [];

        if ($MandatoryLang) {
            Lang::setLocale($MandatoryLang);
            Translator::instance()->setLocale($MandatoryLang);
        }
        TranslateMessage::whereIn('code', $messageKeys)->get()->map(function ($item) use (&$translations) {
            $translations[$item->code] = $item->getContentAttribute();
        });

        $offeredServices = (new Service())->where('offer_id', $offerId)
            ->where('pre', 1)
            ->with('unit')
            ->get();


        if (empty($offeredServices)) {
            return '';
        }

        $message = '';
        $totalAmount = 0;

        foreach ($offeredServices as $index => $service) {
            $amount = $service->amount * $service->unit_price;

            $totalAmount += $amount;

            $message .= ($index+1).') '. $service->title .': '. $service->amount .' '. $service->unit->title
                .' - '. number_format($amount, 2). ' '
                . $translations[$messageBaseKey . 'currency_unit'] . ' (' . $service->unit_price . ' '
                . $translations[$messageBaseKey . 'currency_unit'] . ' ' . $service->unit->title . ') \n ';
        }

        $message .= $translations[$messageBaseKey . 'price_sum'] . ' '. number_format($totalAmount, 2)
            . $translations[$messageBaseKey . 'currency_unit']
            .' \n  '
            . $translations[$messageBaseKey . 'estimated_completed_date']
            . ' '. $endDate.' ';


        return $message;
    }


    /**
     * Accept offer by Job owner.
     */
    public function acceptOffer($lang, $id)
    {
        $authUserId = config('auth.UserID');

        $offer = Offer::find($id);

        if (!$offer) {
            return $this->errorResponse('Not Found', self::$ERROR_CODES['NOT_FOUND']);
        }

        $offer->load(['job', 'services']);

        if($this->getAuthUserType($offer, $authUserId) != 'client' || $offer->status == $offer->statuses['offer_accepted_by_client']){
            return $this->errorResponse('Forbidden', 403);
        }

        $order = $this->transferOfferToOrder($offer);

        $client = $order->getUserByRole($order, 'client');
        $worker = $order->getUserByRole($order, 'worker');

        if ($order->payment_type == $order->paymentTypes['balance']) {
            (new Message)->sendSystemMessage($offer->conversation_id, 'offer_accepted_by_client_pre_pay', [], [], $client->lang);

            $order->update(['status' => $order->statuses['pending_payment']]);

            return (new Payment)->paymentJobOrder($order, $authUserId);
        }



        (new Notification)->sendTemplateNotifications(
            [$worker->id],
            'offerAcceptedByClient',
            [$client->name.' '.$client->surname],
            ['type' => 'job_order', 'id' => $order->id, 'conversation_id' => $offer->conversation_id],
            'chat'
        );

        $offer->update([
            'status' => (new Offer())->statuses['offer_accepted_by_client']
        ]);

        $offer->job()->update([
            'status' => (new Job())->statuses['in_progress']
        ]);

        (new Message)->sendSystemMessage(
            $offer->conversation_id,
            'offer_accepted_by_client',
            ['offer_status_id' => $offer->status],
            [],
            $client->lang
        );

        return $this->successResponse([]);
    }

    /**
     * Reject offer.
     */
    public function rejectOffer($lang, $id)
    {
        $authUserId = config('auth.UserID');

        $offer = Offer::find($id);

        if (!$offer) {
            return $this->errorResponse('Not Found', self::$ERROR_CODES['NOT_FOUND']);
        }

        $offer->load('job');

        $authUserType = $this->getAuthUserType($offer, $authUserId);

        if ($authUserType == 'worker' && $offer->author_id == $authUserId) {
            $this->rejectByWorker($offer);
        } elseif ($authUserType == 'client' && $offer->job->user_id == $authUserId) {
            $this->rejectByClient($offer);
        } else {
            return $this->errorResponse('Forbidden', 403);
        }

        return $this->successResponse([]);
    }

    /**
     * Reject offer by Job owner.
     */
    private function rejectByClient($offer)
    {
        $offer->update([
            'status' => (new Offer())->statuses['offer_rejected_by_client']
        ]);
        $client = $this->getUserByRole($offer, 'client');

        (new Message)->sendSystemMessage(
            $offer->conversation_id,
            'offer_rejected_by_client',
            ['offer_status_id' => $offer->status],
            [],
            $client->lang
        );
    }

    /**
     * Reject offer by worker.
     */
    private function rejectByWorker($offer)
    {
        $client = $this->getUserByRole($offer, 'client');
        $offer->update([
            'status' => (new Offer())->statuses['offer_rejected_by_worker']
        ]);

        (new Message)->sendSystemMessage(
            $offer->conversation_id,
            'offer_rejected_by_worker',
            ['offer_status_id' => $offer->status],
            [],
            $client->lang
        );
    }

    private function transferOfferToOrder($offer)
    {
        $appCommission = (new Worker)->getWorkerCommission($this->getUserByRole($offer, 'worker')->id);

        $bankPercent = $offer->payment_type == (new Order())->paymentTypes['balance']
            ? (int)$this->getConfig('bank_percent')
            : 0;

        $prices = $this->calculateOrderPrices($offer->services, $offer->payment_type, $appCommission);

        $order = Order::create([
            'offer_id' => $offer->id,
            'status' => (new Order)->statuses['work_started'],
            'payment_type' => $offer->payment_type,
            'price'                 => Arr::get($prices, 'order_price'),
            'total_price'           => Arr::get($prices, 'order_price') + Arr::get($prices, 'bank_commission'),
            'bank_percent'          => $bankPercent,
            'bank_percent_amount'   => Arr::get($prices, 'bank_commission'),
            'app_percent'           => $appCommission,
            'app_percent_amount'    => Arr::get($prices, 'app_commission'),
            'estimated_completed_date' => $offer->estimated_completed_date,
        ]);

        $offer->services()->update([
            'order_id' => $order->id
        ]);

        return $order;
    }

    public function calculateOrderPrices($services, $paymentType, $appCommission)
    {
        $price = 0;

        foreach ($services as $service) {
            $price += ($service->amount ?? 0) * $service->unit_price;
        }

        return [
            'order_price'       => $price,
            'bank_commission'   => $paymentType == (new Order())->paymentTypes['balance'] ? ($price / 100 * $this->getConfig('bank_percent')) : 0,
            'app_commission'     => round($price / 100 * $appCommission, 2)
        ];
    }

    private function getAuthUserType($offerInstance, $authUserId)
    {
        $offer = clone $offerInstance;

        $offer->loadMissing(['job']);

        $job = $offer->job;

        if ( !in_array($authUserId, [$offer->author_id, $job->user_id])){
            return '';
        }

        // Determine client and worker IDs based on author roles
        $clientId = ($job->author_role == $job->authorRoles['client']) ? $job->user_id : $offer->author_id;
        $workerId = ($job->author_role == $job->authorRoles['worker']) ? $job->user_id : $offer->author_id;

        // Return the role matching the authUserId
        return $authUserId == $clientId ? 'client' : ($authUserId == $workerId ? 'worker' : '');
    }

    public function getUserByRole($offerInstance, $role)
    {
        $offer = clone $offerInstance;

        $offer->loadMissing(['job']);

        $job = $offer->job;
        $isClientRole = $job->author_role == $job->authorRoles['client'];
        $isWorkerRole = $job->author_role == $job->authorRoles['worker'];

        if ($role === 'client') {
            return User::find($isClientRole ? $job->user_id : $offer->author_id);
        } elseif ($role === 'worker') {
            return User::find($isWorkerRole ? $job->user_id : $offer->author_id);
        }

        return 0;
    }
}
