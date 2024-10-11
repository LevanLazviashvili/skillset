<?php namespace skillset\Marketplace\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Carbon\Carbon;
use Cms\Traits\ApiResponser;
use Cms\Traits\SmsOffice;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use RainLab\User\Models\User;
use RainLab\User\Models\Worker;
use skillset\Configuration\Traits\Config;
use skillset\Conversations\Models\Message;
use skillset\Marketplace\Models\Application;
use skillset\Marketplace\Models\Order;
use skillset\Notifications\Models\Notification;
use skillset\Payments\Models\Payment;
use RainLab\Translate\Models\Message as TranslateMessage;

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
        'marketplace'
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('skillset.Marketplace', 'main-menu-item', 'side-menu-item3');
    }

    public function get(Request $request)
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

        $params = $request->validate($rules);

        $authUserId = config('auth.UserID');

        $query = Order::with(['offer.application.user', 'offer.user'])->where(function ($query) use ($authUserId) {
            return $query->whereHas('offer', function ($q) use ($authUserId) {
                return $q->where('user_id', $authUserId);
            })->orWhereHas('offer.application', function ($q) use ($authUserId) {
                return $q->where('user_id', $authUserId);
            });
        });

        if ($keyword = Arr::get($params, 'keyword')) {
            $query->whereHas('offer.application', function ($query) use ($keyword) {
                return $query->where(function ($q) use ($keyword) {
                    $q->where('title', 'like', '%' . $keyword . '%')
                        ->orWhere('description', 'like', '%' . $keyword . '%');
                });
            });
        }

        $query->orderBy('id', 'desc');

        $orders = $query->paginate($params['per_page'] ?? 10);

        $user = new User();

        $order = new Order();

        $orders->setCollection($orders->getCollection()->map(function ($item) use ($order, $user) {
            $clientInfo = $user->filterInfo($order->getUserByRole($item, 'client'));
            $sellerInfo = $user->filterInfo($order->getUserByRole($item, 'seller'));
            $app = $item->offer->application;

            unset($app->user);

            $item['client'] = $clientInfo;
            $item['seller'] = $sellerInfo;
            $item['application'] = $app;

            $item = $item->toArray();

            unset($item['offer']);
            return $item;
        }));

        return $this->response([
            'orders' => $orders,
        ]);
    }

    public function show($lang, $id)
    {
        $authUserId = config('auth.UserID');

        $order = Order::with([
            'offer.application',
            'offer.user',
            'products',
            'rates'
        ])
            ->where(function ($query) use ($authUserId) {
                return $query->whereHas('offer', function ($q) use ($authUserId) {
                    return $q->where('user_id', $authUserId);
                })->orWhereHas('offer.application', function ($q) use ($authUserId) {
                    return $q->where('user_id', $authUserId);
                });
            })
            ->where('id', $id)
            ->first();

        if(!$order){
            return $this->errorResponse('Not Found', self::$ERROR_CODES['NOT_FOUND']);
        }

        $user = new User();

        $seller = $user->filterInfo($order->getUserByRole($order, 'seller'));
        $client = $user->filterInfo($order->getUserByRole($order, 'client'));

        $order->offer->application->load('user', 'video', 'images');

        $order = $order->toArray();

        unset($order['offer']['user'], $order['offer']['application']['user']);

        $order['seller'] = $seller;
        $order['client'] = $client;

        return $this->response([
            'order' => $order,
        ]);
    }

    public function finishOrderBySeller(Request $request)
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

        $order = Order::with([
            'offer.application.user',
            'offer.products',
            ])
            ->where('id', $validatedData['order_id'])
            ->first();

        if ($this->getAuthUserType($order, $authUserId) != 'seller'){
            return $this->errorResponse('Forbidden', 403);
        }

        $this->saveProducts($order, $validatedData['products']);

        $order->load('products');

        $appCommission = (new Worker)->getWorkerCommission(config('auth.UserID'));

        $prices = $this->calculateOrderPrices($order->products, $order->payment_type, $appCommission);

        $bankPercent = $order->payment_type == $order->paymentTypes['balance']
            ? (int)$this->getConfig('bank_percent')
            : 0;

        $order->update([
            'status'                => $order->statuses['contract_ready'],
            'price'                 => Arr::get($prices, 'order_price'),
            'total_price'           => Arr::get($prices, 'order_price') + Arr::get($prices, 'bank_commission'),
            'bank_percent'          => $bankPercent,
            'bank_percent_amount'   => Arr::get($prices, 'bank_commission'),
            'app_percent'           => $appCommission,
            'app_percent_amount'    => Arr::get($prices, 'app_commission'),
        ]);

        (new Message)->sendSystemMessage(
            $order->offer->conversation_id,
            'contract_is_ready',
            ['order_status_id' => $order->status]
        );

        (new Message)->sendSystemMessage(
            $order->offer->conversation_id,
            'marketplace_contract',
            [],
            ['message' => $this->generateAcceptanceSurrenderMessage($order, false)]
        );

        $client = $order->getUserByRole($order, 'client');
        $seller = $order->getUserByRole($order, 'seller');

        $this->SendSMS($client->username, $this->generateAcceptanceSurrenderMessage($order));

        (new Notification)->sendTemplateNotifications(
            $client->id,
            'marketplaceInvoiceSent',
            [$seller->name.' '.$seller->surname],
            ['type' => 'marketplace_order', 'id' => $order->id, 'conversation_id' => $order->offer->conversation_id],
            'chat'
        );

        return $this->successResponse([]);
    }

    public function finishOrderByClient(Request $request)
    {
        $authUserId = config('auth.UserID');

        $validator = Validator::make($request->all(), ['order_id' => 'required|integer|exists:skillset_marketplace_orders,id']);

        if ($validator->fails()) {
            return $this->errorResponse(
                $validator->getMessageBag(),
                self::$ERROR_CODES['VALIDATION_ERROR'],
                $validator->getMessageBag()
            );
        }

        $order = Order::with('offer.application')->where('id', $request->order_id)->first();

        if (
            $this->getAuthUserType($order, $authUserId) != 'client'
            || $order->status != $order->statuses['contract_ready']
        ){
            return $this->errorResponse('Forbidden', 403);
        }

        $order->update([
            'status'     => $order->statuses['paid'],
            'completed_at'          => Carbon::now()->toDateTimeString()
        ]);

        $order->offer->application->update(['status' => (new Application())->statuses['finished']]);

        $seller = $order->getUserByRole($order, 'seller');
        $client = $order->getUserByRole($order, 'client');

        $appPercent = (new Worker)->getWorkerCommission($seller->id);

        (new Worker)->updateBalance(
            $seller->id,
            $this->getPriceToCharge($order->total_price, $appPercent),
            false
        );

        $messageKey = $order->payment_type == $order->paymentTypes['balance'] ?
            'marketplace_payed_with_balance' :
            'marketplace_payed_with_cash';

        (new Message)->sendSystemMessage(
            $order->offer->conversation_id,
            $messageKey,
            ['order_status_id' => $order->status]
        );

        (new Notification)->sendTemplateNotifications(
            $seller->id,
            'marketplaceInvoiceAccepted',
            [$client->name.' '.$client->surname],
            ['type' => 'marketplace_order', 'id' => $order->id, 'show_rating_popup' => true],
            'marketplace_order_details'
        );

        return $this->successResponse([]);
    }

    public function formAfterSave(Order $order)
    {
        if ($order->status == $order->statuses['paid']) {
            $order->load(['offer.application']);

            $seller = $order->getUserByRole($order, 'seller');
            $client = $order->getUserByRole($order, 'client');

            $appPercent = (new Worker)->getWorkerCommission($seller->id);

            (new Worker)->updateBalance(
                $seller->id,
                $this->getPriceToCharge($order->total_price, $appPercent),
                false
            );

            $messageKey = $order->payment_type == $order->paymentTypes['balance'] ?
                'marketplace_payed_with_balance' :
                'marketplace_payed_with_cash';

            (new Message)->sendSystemMessage(
                $order->offer->conversation_id,
                $messageKey,
                ['order_status_id' => $order->status]
            );

            (new Notification)->sendTemplateNotifications(
                $seller->id,
                'marketplaceInvoiceAccepted',
                [$client->name.' '.$client->surname],
                ['type' => 'marketplace_order', 'id' => $order->id, 'show_rating_popup' => true],
                'marketplace_order_details'
            );
        }
    }

    public function getProducts($lang, $id)
    {
        $authUserId = config('auth.UserID');

        $order = Order::where('id', $id)
            ->where(function ($query) use ($authUserId) {
                $query->whereHas('offer', function ($query) use ($authUserId) {
                    $query->where('user_id', $authUserId);
                })
                    ->orWhereHas('offer.application', function ($query) use ($authUserId) {
                        $query->where('user_id', $authUserId);
                    });
            })
            ->with(['offer.application', 'products.unit'])
            ->first();

        if (!$order) {
            return $this->errorResponse('Not Found', self::$ERROR_CODES['NOT_FOUND']);
        }

        return $this->response([
            'products' => $order->products
        ]);
    }

    private function getAuthUserType($orderInstance, $authUserId)
    {
        $order = clone $orderInstance;

        $order->loadMissing(['offer.application']);

        $offer = $order->offer;
        $app = $order->offer->application;


        if ( !in_array($authUserId, [$offer->user_id, $app->user_id])){
            return '';
        }

        if(($app->trade_type == $app->tradeTypes['buy'] && $offer->user_id == $authUserId) ||
            ($app->trade_type == $app->tradeTypes['sell'] && $app->user_id == $authUserId)){
            return 'seller';
        }elseif (($app->trade_type == $app->tradeTypes['buy'] && $app->user_id == $authUserId) ||
            ($app->trade_type == $app->tradeTypes['sell'] && $offer->user_id == $authUserId)){
            return 'client';
        }

        return 0;
    }

    public function getPriceToCharge($price, $appPercent)
    {
        return $price * $appPercent / 100;
    }

    public function pay($lang, $id)
    {
        $authUserId = config('auth.UserID');

        $order = Order::find($id);

        if(!$order){
            return $this->errorResponse('Not Found', self::$ERROR_CODES['NOT_FOUND']);
        }

        if (
            $order->payment_type != $order->paymentTypes['balance'] ||
            $this->getAuthUserType($order, $authUserId) != 'client'
        ){
            return $this->errorResponse('Forbidden', 403);
        }

        return (new Payment)->paymentMarketplaceOrder($order, $authUserId);
    }

    private function invoiceRules()
    {
        Return [
            'order_id' => 'required|integer|exists:skillset_marketplace_orders,id',
            'products' => 'required|array',
            'products.*.title' => 'required|string',
            'products.*.amount' => 'required|numeric|min:0',
            'products.*.unit_id' => 'required|integer|exists:skillset_details_units,id',
            'products.*.unit_price' => 'required|numeric',
        ];
    }
    public function saveProducts($order, $productsData)
    {
        $order->products()->where('pre', false)->delete();

        $productsDataMapped = collect($productsData)->map(function ($item) use ($order) {
            $item['offer_id'] = $order->offer_id;
            $item['pre'] = false;

            return $item;
        })->toArray();

        $order->products()->createMany($productsDataMapped);
    }

    public function calculateOrderPrices($products, $paymentType, $appCommission)
    {
        $price = 0;

        foreach ($products as $product) {
            $price += ($product->amount ?? 0) * $product->unit_price;
        }

        return [
            'order_price'       => $price,
            'bank_commission'   => $paymentType == (new Order())->paymentTypes['balance'] ? ($price / 100 * $this->getConfig('bank_percent')) : 0,
            'app_commission'     => round($price / 100 * $appCommission, 2)
        ];
    }

    private function generateAcceptanceSurrenderMessage($order, $phone=true)
    {
        $messageBaseKey = 'system_messages.acceptance_delivery_message_';

        $messageKeys = [
            $messageBaseKey . 'greeting',
            $messageBaseKey . 'price_sum',
            $messageBaseKey . 'currency_unit',
            $messageBaseKey . 'marketplace_recommend',
        ];

        $translations = [];

        TranslateMessage::whereIn('code', $messageKeys)->get()->map(function ($item) use (&$translations) {
            $translations[$item->code] = $item->getContentAttribute();
        });

        $message = $phone ? $translations[$messageBaseKey . 'greeting'] . " \n" : "";

        if (!$order) {
            return '';
        }

        foreach ($order->products as $product) {
            $message .= "\n". $product->title .' '
                . $product->amount
                . $product->unit->title .' - '
                . $product->unit_price . $translations[$messageBaseKey . 'currency_unit'];
        }

        $message .= "\n\n" . $translations[$messageBaseKey . 'price_sum'] . " ". $order->total_price . $translations[$messageBaseKey . 'currency_unit'];

        $message .= $phone ? "\n\n" .  $translations[$messageBaseKey . 'marketplace_recommend']: "";

        return $message;
    }

    public function onRelationManageUpdate($id)
    {
        parent::onRelationManageUpdate();

        $order = Order::with('products')->where('id', $id)->get();

        $seller = $order->getUserByRole($order, 'seller');

        $appPercent = (new Worker)->getWorkerCommission($seller->id);

        $paymentType = $order->payment_type;

        $prices = $this->calculateOrderPrices($order->products, $paymentType, $appPercent);

        $order->update([
            'price'                 => Arr::get($prices, 'order_price'),
            'total_price'           => Arr::get($prices, 'order_price') + Arr::get($prices, 'bank_commission'),
            'bank_percent'          => $paymentType == $order->paymentTypes['balance'] ? (int) $this->getConfig('bank_percent') : 0,
            'bank_percent_amount'   => Arr::get($prices, 'bank_commission'),
            'app_percent'           => $appPercent,
            'app_percent_amount'    => Arr::get($prices, 'app_commission'),
        ]);

        return Redirect::refresh();
    }
}
