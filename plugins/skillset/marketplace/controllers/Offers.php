<?php namespace skillset\marketplace\controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Carbon\Carbon;
use Cms\Traits\ApiResponser;
use Cms\Traits\SmsOffice;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use RainLab\Translate\Classes\Translator;
use RainLab\Translate\Models\Message as TranslateMessage;
use RainLab\User\Models\User;
use RainLab\User\Models\Worker;
use skillset\Configuration\Traits\Config;
use skillset\Conversations\Models\Message;
use skillset\Marketplace\Models\Application;
use skillset\Marketplace\Models\Offer;
use skillset\Marketplace\Models\Order;
use skillset\Marketplace\Models\Product;
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
        'marketplace'
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('skillset.Marketplace', 'main-menu-item', 'side-menu-item2');
    }

    public function products(Request $request)
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

        $validatedData = $request->validate($rules);;

        $offer = Offer::find($validatedData['offer_id']);

        $offer->load('application.user');

        if (
            !$this->isAuthUserSeller($offer, $authUserId) ||
            !in_array($authUserId, [$offer->user_id, $offer->application->user_id])
        ) {
            return $this->errorResponse('Forbidden', 403);
        }

        $this->saveProducts($offer, $validatedData['products']);

        $client = $this->getUserByRole($offer, 'client');
        $seller = $this->getUserByRole($offer, 'seller');

        $systemMessage = $this->generateOrderProductsMsg($offer->id, $client->lang);


        $this->SendSMS($client->username, vsprintf((new Message)->getMessageText('offered_products'), [$systemMessage]));

        $offer->update([
            'payment_type' => $validatedData['payment_type'],
            'status' => (new Offer())->statuses['offer_invoice_sent']
        ]);

        (new Message)->sendSystemMessage(
            $offer->conversation_id,
            'offered_products',
            ['offer_status_id' => $offer->status],
            ['message' => $systemMessage],
            $client->lang
        );

        (new Notification)->sendTemplateNotifications(
            [$client->id],
            'marketplacePreInvoiceSent',
            [$seller->name.' '.$seller->surname],
            ['type' => 'marketplace_offer', 'id' => $offer->id, 'conversation_id' => $offer->conversation_id],
            'chat'
        );

        return $this->successResponse([]);
    }

    public function saveProducts($offer, $productsData)
    {
        $offer->products()->delete();

        $offer->products()->createMany($productsData);
    }

    public function getProducts($lang, $id)
    {
        $authUserId = config('auth.UserID');

        $offer = Offer::where('id', $id)
            ->where(function ($query) use ($authUserId) {
                $query->where('user_id', $authUserId)
                    ->orWhereHas('application', function ($query) use ($authUserId) {
                        $query->where('user_id', $authUserId);
                    });
            })
            ->with([
                'application',
                'products' => function ($query) {
                    $query->with('unit')->where('pre', 1);
                }
            ])
            ->first();

        if(!$offer){
            return $this->errorResponse('Not Found', self::$ERROR_CODES['NOT_FOUND']);
        }

        return $this->response([
            'products' => $offer->products
        ]);
    }

    private function invoiceRules()
    {
        Return [
            'offer_id' => 'required|integer|exists:skillset_marketplace_offers,id',
            'payment_type' => 'required|integer|in:0,1',
            'products' => 'required|array',
            'products.*.title' => 'required|string',
            'products.*.amount' => 'required|numeric|min:0',
            'products.*.unit_id' => 'required|integer|exists:skillset_details_units,id',
            'products.*.unit_price' => 'required|numeric',
        ];
    }

    public function generateOrderProductsMsg($offerId, $MandatoryLang = null)
    {
        $messageBaseKey = 'system_messages.invoice_';

        $messageKeys = [
            $messageBaseKey . 'price_sum',
            $messageBaseKey . 'currency_unit',
        ];

        $translations = [];

        if ($MandatoryLang) {
            Lang::setLocale($MandatoryLang);
            Translator::instance()->setLocale($MandatoryLang);
        }

        TranslateMessage::whereIn('code', $messageKeys)->get()->map(function ($item) use (&$translations) {
            $translations[$item->code] = $item->getContentAttribute();
        });

        $offeredProducts = (new Product())->where('offer_id', $offerId)
            ->with('unit')
            ->get();

        if (empty($offeredProducts)) {
            return '';
        }

        $message = '';
        $totalAmount = 0;

        foreach ($offeredProducts as $index => $product) {
            $amount = $product->amount * $product->unit_price;

            $totalAmount += $amount;

            $message .= ($index+1).') '. $product->title .': '. $product->amount .' '. $product->unit->title
                .' - '. number_format($amount, 2).' ლ. (' . $product->unit_price . ' ლ. ' . $product->unit->title . ') \n ';
        }

        $message .= $translations[$messageBaseKey . 'price_sum'] . ' ' . number_format($totalAmount, 2).' ლ.';

        return $message;
    }


    /**
     * Accept offer.
     */
    public function accept($lang, $id)
    {
        $authUserId = config('auth.UserID');

        $offer = Offer::find($id);

        if (!$offer) {
            return $this->errorResponse('Not Found', self::$ERROR_CODES['NOT_FOUND']);
        }

        $offer->load('application');

        if ($this->isAuthUserClient($offer, $authUserId)) {
            $order = $this->transferOfferToOrder($offer);

            $order->load('products');

            $client = $this->getUserByRole($offer, 'client');
            $seller = $this->getUserByRole($offer, 'seller');

            if ($order->payment_type == $order->paymentTypes['balance']) {
                $order->update(['status' => $order->statuses['pending_payment']]);

                (new Message)->sendSystemMessage(
                    $offer->conversation_id,
                    'marketplace_offer_accepted_pre_pay',
                    ['order_status_id' => $order->status],
                    [],
                    $client->lang
                );

                (new Notification)->sendTemplateNotifications(
                    [$seller->id],
                    'marketplacePreInvoiceAccepted',
                    [$client->name.' '.$client->surname],
                    ['type' => 'marketplace_offer', 'id' => $offer->id, 'conversation_id' => $offer->conversation_id],
                    'chat'
                );

                return (new Payment)->paymentMarketplaceOrder($order, $authUserId);
            }

            $offer->update([
                'status' => (new Offer())->statuses['offer_accepted']
            ]);

            $offer->application()->update([
                'status' => (new Application())->statuses['processing']
            ]);

            (new Message)->sendSystemMessage(
                $offer->conversation_id,
                'marketplace_offer_accepted',
                ['offer_status_id' => $offer->status],
                [],
                $client->lang
            );

            (new Notification)->sendTemplateNotifications(
                [$seller->id],
                'marketplacePreInvoiceAccepted',
                [$client->name.' '.$client->surname],
                ['type' => 'marketplace_offer', 'id' => $offer->id, 'conversation_id' => $offer->conversation_id],
                'chat'
            );
        }else{
            return $this->errorResponse('Forbidden', 403);

        }

        return $this->successResponse([]);
    }

    /**
     * Reject offer.
     */
    public function reject($lang, $id)
    {
        $authUserId = config('auth.UserID');

        $offer = Offer::find($id);

        if (!$offer) {
            return $this->errorResponse('Not Found', self::$ERROR_CODES['NOT_FOUND']);
        }

        if ($this->isAuthUserClient($offer, $authUserId)) {
            $offer->update([
                'status' => (new Offer())->statuses['offer_rejected']
            ]);

            $client = $this->getUserByRole($offer, 'client');
            $seller = $this->getUserByRole($offer, 'seller');

            (new Message)->sendSystemMessage(
                $offer->conversation_id,
                'marketplace_offer_rejected',
                ['offer_status_id' => $offer->status],
                $client->lang
            );

            (new Notification)->sendTemplateNotifications(
                [$seller->id],
                'marketplacePreInvoiceRejected',
                [$client->name.' '.$client->surname],
                ['type' => 'marketplace_offer', 'id' => $offer->id, 'conversation_id' => $offer->conversation_id],
                'chat'
            );
        } else {
            return $this->errorResponse('Forbidden', 403);
        }

        return $this->successResponse([]);
    }

    private function transferOfferToOrder($offer)
    {
        $appCommission = (new Worker)->getWorkerCommission(config('auth.UserID'));

        $bankPercent = $offer->payment_type == (new Order())->paymentTypes['balance']
            ? (int)$this->getConfig('bank_percent')
            : 0;

        $offer->loadMissing('products');

        $prices = $this->calculateOrderPrices($offer->products, $offer->payment_type, $appCommission);

        $order = Order::create([
            'offer_id' => $offer->id,
            'payment_type' => $offer->payment_type,
            'status' => (new Order)->statuses['pending'],
            'price'                 => Arr::get($prices, 'order_price'),
            'total_price'           => Arr::get($prices, 'order_price') + Arr::get($prices, 'bank_commission'),
            'bank_percent'          => $bankPercent,
            'bank_percent_amount'   => Arr::get($prices, 'bank_commission'),
            'app_percent'           => $appCommission,
            'app_percent_amount'    => Arr::get($prices, 'app_commission'),
        ]);

        $offer->products()->update([
            'order_id' => $order->id
        ]);

        return $order;
    }

    private function isAuthUserClient($offerInstance, $authUserId)
    {
        $offer = clone $offerInstance;

        $offer->loadMissing('application');

        $app = $offer->application;

        return ($app->trade_type == $app->tradeTypes['buy'] && $app->user_id == $authUserId) ||
            ($app->trade_type == $app->tradeTypes['sell'] && $offer->user_id == $authUserId);
    }

    private function isAuthUserSeller($offerInstance, $authUserId)
    {
        $offer = clone $offerInstance;

        $offer->loadMissing('application');

        $app = $offer->application;

        return ($app->trade_type == $app->tradeTypes['buy'] && $offer->user_id == $authUserId) ||
            ($app->trade_type == $app->tradeTypes['sell'] && $app->user_id == $authUserId);
    }

    private function getUserByRole($offerInstance, $role)
    {
        $offer = clone $offerInstance;

        $offer->loadMissing(['application']);

        $app = $offer->application;
        $isAuthorSeller = $app->trade_type == $app->tradeTypes['sell'];

        if ($role === 'seller') {
            return User::find($isAuthorSeller ? $app->user_id : $offer->user_id);
        }else if ($role === 'client') {
            return User::find(!$isAuthorSeller ? $app->user_id : $offer->user_id);
        }

        return 0;
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
}
