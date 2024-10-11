<?php namespace skillset\Payments\Models;

use Cms\Traits\ApiResponser;
use Illuminate\Http\Request;
use Model;
use October\Rain\Support\Arr;
use skillset\Configuration\Traits\Config;
use skillset\Jobs\Models\Order as JobOrder;
use skillset\Marketplace\Models\Order as MarketOrder;
use skillset\Orders\Models\FillBalanceOrder;
use skillset\orders\models\Advert;
use Vdomah\JWTAuth\Models\User;

/**
 * Model
 */
class Payment extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use ApiResponser;
    use Config;

    public $paymentTypes = [
        'order'                 => 1,
        'fill_balance'          => 2,
        'job_order'             => 3,
        'marketplace_order'     => 4,
        'vip_job'               => 5,
        'vip_marketplace_app'   => 6,
    ];

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = true;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'skillset_payments_';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $belongsTo = [
        'User'    => [User::class, 'key' => 'user_id', 'otherKey' => 'id']
    ];

    public function paymentOrder($Order)
    {
        $OrderServices = $Order->OrderServices()->get()->toArray();
        $Response = $this->makePayment($Order->id, $Order->client_id, $OrderServices, $Order->bank_percent_amount, $Order->total_price, $this->paymentTypes['order']);

        return $this->processPaymentResponse($Response, $Order);
    }

    public function paymentJobOrder(JobOrder $order, $userId)
    {
        $response = $this->makePayment($order->id, $userId, [], $order->bank_percent_amount, $order->total_price, $this->paymentTypes['job_order']);

        return $this->processPaymentResponse($response, $order);
    }

    public function paymentMarketplaceOrder(MarketOrder $order, $userId)
    {
        $response = $this->makePayment($order->id, $userId, [], $order->bank_percent_amount, $order->total_price, $this->paymentTypes['marketplace_order']);

        return $this->processPaymentResponse($response, $order);
    }

    private function processPaymentResponse($response, $order)
    {
        $order->payment_hash = Arr::get($response, 'payment_hash');
        $order->payment_order_id = Arr::get($response, 'order_id');
        $order->save();

        if (!Arr::get($response, 'order_id')) {
            return $this->errorResponse(Arr::get($response, 'error_message'), self::$ERROR_CODES['PAYMENT_ERROR']);
        }

        $return = [
            'order_id'  => Arr::get($response, 'order_id'),
            'href'      => Arr::get($response, 'links.1.href')
        ];

        if (env('APP_DEBUG')) {
            $return['hash'] = Arr::get($response, 'payment_hash');
        }

        return $return;
    }

    public function fillBalance($UserID, $Amount)
    {
        $OrderServices = [
            [
                'unit_price'    => $Amount,
                'title'         => 'ბალანსის შევსება',
                'amount'        => 1,
                'product_id'    => 0
            ]
        ];
        $Order = (new FillBalanceOrder)->create([
            'user_id'   => $UserID,
            'amount'    => $Amount,
        ]);
        $BankPercent = $Amount*$this->getConfig('bank_percent')/100;
        $Response = $this->makePayment($Order->id, $UserID, $OrderServices, $BankPercent,$Amount+$BankPercent, $this->paymentTypes['fill_balance']);
        $Order->payment_hash = Arr::get($Response, 'payment_hash');
        $Order->payment_order_id = Arr::get($Response, 'order_id');
        $Order->save();
        $Return = [
            'href'      => Arr::get($Response, 'links.1.href')
        ];
        return $Return;
    }

    public function buyVip($app, $type)
    {
        $amount = 0;
        $paymentType = '';

        switch ($type){
            case 'job':
                $amount = $this->getConfig('job_vip_price');
                $paymentType = $this->paymentTypes['vip_job'];
                break;
            case 'marketplace':
                $amount = $this->getConfig('marketplace_vip_price');
                $paymentType = $this->paymentTypes['vip_marketplace_app'];
                break;

        }

        $bankPercent = $this->getConfig('bank_percent') ?? 0;
        $bankPercentAmount = ($amount * $this->getConfig('bank_percent') / 100) ?? 0;

        $order = $app->adverts()->create([
            'price' => $amount,
            'total_price' => $amount + $bankPercentAmount,
            'bank_percent' => $bankPercent,
            'bank_percent_amount' => $bankPercentAmount,
        ]);

        $response = $this->makePayment(
            $order->id,
            $app->user_id,
            [],
            $bankPercentAmount,
            $amount + $bankPercentAmount,
            $paymentType
        );

        return [
            'href'      => Arr::get($response, 'links.1.href')
        ];
    }

    private function makePayment($OrderID, $UserID, $OrderServices, $BankPercentAmount, $TotalPrice, $PaymentType)
    {
        $BankPercentAmount = round($BankPercentAmount, 2);
        $TotalPrice = round($TotalPrice, 2);
        $Lang = $this->detectLang();
        $Request = [
            "intent"                        => "AUTHORIZE",
            "locale"                        => $Lang,
            "shop_order_id"                 => $OrderID,
            "redirect_url"                  => config('app.url').$Lang."/payments/response?order_id=".$OrderID.'&payment_type='.$PaymentType,
            "show_shop_order_id_on_extract" => true,
            "capture_method"                => "AUTOMATIC",
            "items"                         => [],
            "purchase_units"                => []
        ];

//        foreach ($OrderServices as $Service)
//        {
//            $Request['items'][] = [
//                "amount" => (float) Arr::get($Service, 'unit_price'),
//                "description" => Arr::get($Service,'title'),
//                "quantity" => (int)Arr::get($Service, 'amount'),
//                "product_id" => Arr::get($Service, 'id')
//            ];
//        }
//        if ($BankPercentAmount) {
//            $Request['items'][] = [
//                "amount" => $BankPercentAmount,
//                "description" => 'ბანკის საკომისიო',
//                "quantity" => 1,
//                "product_id" => 0
//            ];
//        }


        $Request["purchase_units"][] = [
            "amount" => [
                "currency_code" => "GEL",
                "value" => $TotalPrice
            ]
        ];
        traceLog($Request);
        $Response = $this->sendCurl(config('app.payments.bog.order_url'), 'POST', [
            'Authorization: Bearer '.$this->getToken(),
            'Content-Type: application/json'
        ], $Request, 'json');

        traceLog($Response);

        self::create([
            'payment_hash'         => Arr::get($Response, 'payment_hash'),
            'payment_order_id'     => Arr::get($Response, 'order_id'),
            'order_id'             => $OrderID,
            'status'               => strtolower(Arr::get($Response, 'status')),
            'user_id'              => $UserID,
            'price'                => $TotalPrice,
            'ip'                   => $_SERVER['REMOTE_ADDR'],
            'payment_type'         => $PaymentType
        ]);

        return $Response;
    }

    public function getPaymentStatus($OrderID, $PaymentType)
    {
        $Payment = self::where('order_id', $OrderID)->where('payment_type', $PaymentType)->orderBy('id', 'desc')->first();
        if (!$Payment) {
            throw new \Exception('not found', self::$ERROR_CODES['NOT_FOUND']);
        }
        $url = sprintf(config('app.payments.bog.order_details'),Arr::get($Payment, 'payment_order_id'));
        $PaymentData = $this->sendCurl($url, 'GET', [
            'Authorization: Bearer '.$this->getToken(),
            'Content-Type: application/json'
        ]);
        if (!Arr::get($PaymentData, 'error_code')) {
            $Payment->update(['status' => strtolower(Arr::get($PaymentData, 'status'))]);
        }
        return [
            'order_id'          => $OrderID,
            'payment_status'    => $this->translate('payment status : '.strtolower($Payment->status))
        ];
    }

    public function getToken()
    {
        $Response = $this->sendCurl(config('app.payments.bog.auth_url'), 'POST', [
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Basic '.base64_encode(config('app.payments.bog.client_id').':'.config('app.payments.bog.secret_key'))
        ], [
            'grant_type'  => 'client_credentials',
        ]);
        if (!$Response) {
            return false;
        }
        return Arr::get($Response, 'access_token');
    }

    public function sendCurl($url, $requestType = 'GET', $headers = [], $data = [], $dataType = 'form-data')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL,$url);
        if ($requestType == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataType == 'json' ? json_encode($data) : http_build_query($data));
        }
        tracelog($dataType == 'json' ? json_encode($data) : http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close ($ch);
        if (!$response) {
            return false;
        }
        if (!$Json = json_decode($response, true)) {
            return false;
        }
        return $Json;

    }


}
