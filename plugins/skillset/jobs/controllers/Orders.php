<?php namespace skillset\Jobs\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Carbon\Carbon;
use Cms\Traits\ApiResponser;
use Cms\Traits\SmsOffice;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Pheanstalk\Exception;
use RainLab\Translate\Classes\Translator;
use RainLab\Translate\Models\Message as TranslateMessage;
use RainLab\User\Models\User;
use RainLab\User\Models\Worker;
use skillset\Configuration\Traits\Config;
use skillset\Conversations\Models\Message;
use skillset\Jobs\Models\Job;
use skillset\Jobs\Models\Order;
use skillset\Notifications\Models\Notification;
use skillset\Payments\Models\Payment;

class Orders extends Controller
{
    use ApiResponser;
    use SmsOffice;
    use Config;

    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController',
        'Backend\Behaviors\RelationController',
    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $relationConfig = 'config_relation.yaml';

    public $requiredPermissions = [
        'jobs' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('skillset.Jobs', 'main-menu-item', 'side-menu-item3');
    }

    public function finishOrderByWorker(Request $request, Order $orderModel)
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

        $order = Order::find($validatedData['order_id']);

        $order->load([
            'offer.job.user',
            'offer.services'
        ]);

        if ($this->getUserRole($order, $authUserId) != 'worker'){
            return $this->errorResponse('Forbidden', 403);
        }

        if ($order->payment_type == $orderModel->paymentTypes['balance']){
            $services = $order->offer->services->toArray();
        } else {
            $services = $validatedData['services'];
        }

        $this->saveServices($order, $services);

        $appCommission = (new Worker)->getWorkerCommission($authUserId);

        $order->load('services');

        $prices = $this->calculateOrderPrices($order->services, $order->payment_type, $appCommission);

        $bankPercent = $order->payment_type == $orderModel->paymentTypes['balance']
            ? (int)$this->getConfig('bank_percent')
            : 0;

        $order->update([
            'status'                => $orderModel->statuses['work_finished_by_worker'],
            'price'                 => Arr::get($prices, 'order_price'),
            'total_price'           => Arr::get($prices, 'order_price') + Arr::get($prices, 'bank_commission'),
            'bank_percent'          => $bankPercent,
            'bank_percent_amount'   => Arr::get($prices, 'bank_commission'),
            'app_percent'           => $appCommission,
            'app_percent_amount'    => Arr::get($prices, 'app_commission'),
            'completed_at'          => Carbon::now()->toDateTimeString()
        ]);

        $client = $order->getUserByRole($order, 'client');

        (new Message)->sendSystemMessage(
            $order->offer->conversation_id,
            'contract_is_ready',
            ['order_status_id' => $orderModel->statuses['work_finished_by_worker']],
            [],
            $client->lang
        );

        (new Notification)->sendTemplateNotifications(
            $client->id,
            'unPaidOrder',
            [],
            ['type' => 'job_order', 'id' => $order->id, 'conversation_id' => $order->offer->conversation_id],
            'chat'
        );

        $client = $order->getUserByRole($order, 'client');

        (new Message)->sendSystemMessage(
            $order->offer->conversation_id,
            'job_contract',
            [],
            ['message' => $this->generateAcceptanceSurrenderMessage($order, false,  $client->lang)],
            $client->lang
        );

        $this->SendSMS($client->username, $this->generateAcceptanceSurrenderMessage($order));

        return $this->successResponse([]);
    }


    public function finishOrderByClient(Request $request, Order $orderModel)
    {
        $authUserId = config('auth.UserID');

        $user = User::find($authUserId);

        $validator = Validator::make($request->all(), ['order_id' => 'required|integer|exists:skillset_jobs_orders,id']);

        if ($validator->fails()) {
            return $this->errorResponse(
                $validator->getMessageBag(),
                self::$ERROR_CODES['VALIDATION_ERROR'],
                $validator->getMessageBag()
            );
        }

        $order = Order::with('offer.job')->where('id', $request->order_id)->first();

        if ($this->getUserRole($order, $authUserId) != 'client'){
            return $this->errorResponse('Forbidden', 403);
        }

        $order->update([
            'status'     => $orderModel->statuses['paid'],
        ]);

        $worker = $order->getUserByRole($order, 'worker');
        $client = $order->getUserByRole($order, 'client');

        $appPercent = (new Worker)->getWorkerCommission($worker->id);

        (new Notification)->sendTemplateNotifications(
            $worker->id,
            'userAcceptedOrder',
            [$user->name. ' '.$user->surname],
            ['type' => 'job_order', 'id' => $order->id, 'show_rating_popup' => true],
            'job_order_details'
        );

        $order->offer->job->update(['status' => (new Job())->statuses['finished']]);

        (new Worker)->updateBalance(
            $worker->id,
            $this->getPriceToCharge($order->total_price, $appPercent),
            false
        );

        $messageKey = $order->payment_type == $orderModel->paymentTypes['balance'] ?
            'job_finished_payed_with_balance' :
            'job_finished_payed_with_cash';

        (new Message)->sendSystemMessage(
            $order->offer->conversation_id,
            $messageKey,
            ['order_status_id' => $orderModel->statuses['paid']],
            [],
            $client->lang
        );

        return $this->successResponse([]);
    }

    public function formAfterSave(Order $order)
    {

        if ($order->status == $order->statuses['paid']) {
            $order->load(['offer.job']);

            $worker = $order->getUserByRole($order, 'worker');
            $client = $order->getUserByRole($order, 'client');

            $appPercent = (new Worker)->getWorkerCommission($worker->id);

//            (new Notification)->sendTemplateNotifications([$Order->worker_id], 'userAcceptedOrder', [$User->name.' '.$User->surname], ['type' => 'order', 'id' => Arr::get($params, 'order_id')] ,'order_details');

            (new Worker)->updateBalance(
                $worker->id,
                $this->getPriceToCharge($order->total_price, $appPercent),
                false
            );

            $messageKey = $order->payment_type == $order->paymentTypes['balance'] ?
                'job_finished_payed_with_balance' :
                'job_finished_payed_with_cash';

            (new Message)->sendSystemMessage(
                $order->offer->conversation_id,
                $messageKey,
                ['order_status_id' => $order->status],
                [],
                $client->lang
            );
        }
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

    public function getPriceToCharge($price, $appPercent)
    {
        return $price * $appPercent / 100;
    }

    private function generateAcceptanceSurrenderMessage($order, $phone=true, $MandatoryLang = null)
    {
        $messageBaseKey = 'system_messages.acceptance_delivery_message_';

        $messageKeys = [
            $messageBaseKey . 'greeting',
            $messageBaseKey . 'price_sum',
            $messageBaseKey . 'currency_unit',
            $messageBaseKey . 'job_recommend',
        ];

        if ($MandatoryLang) {
            Lang::setLocale($MandatoryLang);
            Translator::instance()->setLocale($MandatoryLang);
        }

        $translations = [];


        TranslateMessage::whereIn('code', $messageKeys)->get()->map(function ($item) use (&$translations) {
            $translations[$item->code] = $item->getContentAttribute();
        });

        $message = $phone ? $translations[$messageBaseKey . 'greeting'] . " \n" : "";

        if (!$order) {
            return '';
        }

        foreach ($order->services as $service) {
            $message .= "\n". $service->title .' '
                . $service->amount
                . $service->unit->title .' - '
                . $service->unit_price. $translations[$messageBaseKey . 'currency_unit'];
        }

        $message .= "\n\n" . $translations[$messageBaseKey . 'price_sum'] . " ". $order->total_price . $translations[$messageBaseKey . 'currency_unit'];

        $message .= $phone ? "\n\n" .  $translations[$messageBaseKey . 'job_recommend']: "";

        return $message;
    }

    private function invoiceRules()
    {
        Return [
            'order_id'                   => 'required|integer|exists:skillset_jobs_orders,id',
            'services'                   => 'required|array',
            'services.*.title'           => 'required|string',
            'services.*.amount'          => 'required|numeric|min:0',
            'services.*.unit_id'         => 'required|integer|exists:skillset_details_units,id',
            'services.*.unit_price'      => 'required|numeric',
        ];
    }

    public function saveServices($order, $servicesData)
    {
        $order->services()->where('pre', false)->delete();

        $servicesDataMapped = collect($servicesData)->map(function ($item) use ($order) {
            $item['offer_id'] = $order->offer_id;
            $item['pre'] = false;

            return $item;
        })->toArray();

        $order->services()->createMany($servicesDataMapped);
    }

    public function getServices($lang, $id)
    {
        $authUserId = config('auth.UserID');

        $order = Order::where(function ($query) use ($authUserId) {
            $query->whereHas('offer', function ($query) use ($authUserId) {
                return $query->where('author_id', $authUserId);
            })
                ->orWhereHas('offer.job', function ($query) use ($authUserId) {
                    return $query->where('user_id', $authUserId);
                });
        })
            ->with([
                'offer.job',
                'services.unit'
            ])
            ->where('id', $id)
            ->first();

        if (!$order) {
            return $this->errorResponse('Not Found', self::$ERROR_CODES['NOT_FOUND']);
        }

        return $this->response([
            'services' => $order->services
        ]);
    }

    public function get(Request $request, Order $order)
    {
        $rules = [
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1',
            'keyword' => 'sometimes|string',
            'status' => 'sometimes|integer|min:1',
        ];

        $data = $request->only(array_keys($rules));

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return $this->errorResponse(
                $validator->getMessageBag(),
                self::$ERROR_CODES['VALIDATION_ERROR'],
                $validator->getMessageBag()
            );
        }

        $validatedParams = $request->validate($rules);

        return $this->response([
            'orders' => $order->getData($validatedParams),
        ]);
    }

    public function show($lang, $id)
    {
        $authUserId = config('auth.UserID');

        $order = Order::with([
            'offer.job',
            'offer.author',
            'services',
            'rates'
        ])
            ->where(function ($query) use ($authUserId) {
                return $query->whereHas('offer', function ($q) use ($authUserId) {
                    return $q->where('author_id', $authUserId);
                })->orWhereHas('offer.job', function ($q) use ($authUserId) {
                    return $q->where('user_id', $authUserId);
                });
            })
            ->where('id', $id)
            ->first();

        if(!$order){
            return $this->errorResponse('Not Found', self::$ERROR_CODES['NOT_FOUND']);
        }

        $user = new User();

        $worker = $user->filterInfo($order->getUserByRole($order, 'worker'));
        $client = $user->filterInfo($order->getUserByRole($order, 'client'));

        $order->offer->job->load('user', 'video', 'images');

        $order = $order->toArray();

        unset($order['offer']['author'], $order['offer']['job']['user']);

        $order['worker'] = $worker;
        $order['client'] = $client;

        return $this->response([
            'order' => $order,
        ]);
    }

    public function pay($lang, $id)
    {
        $authUserId = config('auth.UserID');

        $order = Order::find($id);

        $client = (new Order())->getUserByRole($order, 'client');

        if(!$order){
            return $this->errorResponse('Not Found', self::$ERROR_CODES['NOT_FOUND']);
        }

        if ($order->payment_type != $order->paymentTypes['balance'] || $client->id != $authUserId){
            return $this->errorResponse('Forbidden', 403);
        }

        return (new Payment)->paymentJobOrder($order, $authUserId);
    }

    public function getUserRole($orderInstance, $userId)
    {
        $order = clone $orderInstance;

        $order->loadMissing(['offer.job']);

        $job = $order->offer->job;

        if (!in_array($userId, [$order->offer->author_id, $job->user_id])){
            return '';
        }

        // Determine client and worker IDs based on author roles
        $clientId = ($job->author_role == $job->authorRoles['client']) ? $job->user_id : $order->offer->author_id;
        $workerId = ($job->author_role == $job->authorRoles['worker']) ? $job->user_id : $order->offer->author_id;

        // Return the role matching the userId
        return $userId == $clientId ? 'client' : ($userId == $workerId ? 'worker' : '');
    }

    public function onRelationManageUpdate($id)
    {
        parent::onRelationManageUpdate();

        $order = Order::with('services')->where('id', $id)->get();

        $worker = $order->getUserByRole($order, 'worker');

        $appPercent = (new Worker)->getWorkerCommission($worker->id);

        $paymentType = $order->payment_type;

        $prices = $this->calculateOrderPrices($order->services, $paymentType, $appPercent);

        $order->update([
            'price'                 => Arr::get($prices, 'order_price'),
            'total_price'           => Arr::get($prices, 'order_price') + Arr::get($prices, 'bank_commission'),
            'bank_percent'          =>$paymentType == $order->paymentTypes['balance'] ? (int) $this->getConfig('bank_percent') : 0,
            'bank_percent_amount'   => Arr::get($prices, 'bank_commission'),
            'app_percent'           => $appPercent,
            'app_percent_amount'    => Arr::get($prices, 'app_commission'),
        ]);

        return Redirect::refresh();
    }
}
