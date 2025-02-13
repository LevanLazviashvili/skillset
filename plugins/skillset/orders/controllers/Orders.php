<?php namespace skillset\Orders\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Cms\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RainLab\User\Models\User;
use RainLab\User\Models\Worker;
use skillset\Configuration\Traits\Config;
use skillset\Conversations\Models\Conversation;
use skillset\Conversations\Models\Message;
use skillset\Notifications\Models\Notification;
use skillset\Orders\Models\Order;
use skillset\Payments\Models\Payment;
use Tymon\JWTAuth\Facades\JWTAuth;

class Orders extends Controller
{
    use ApiResponser;
    use Config;
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController',
        'Backend\Behaviors\RelationController',
        '\Backend\Behaviors\ImportExportController'
    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $relationConfig = 'config_relation.yaml';
    public $importExportConfig = 'config_import_export.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('skillset.Orders', 'main-menu-item');
    }

    public function onRelationManageUpdate($id)
    {
        parent::onRelationManageUpdate();
        $Order = (new Order)->find($id);
        $AppPercent = (new Worker)->getWorkerCommission($Order->worker_id);
        $OrderServices = $Order->OrderServices()->get()->toArray();
        $paymentType = $Order->payment_type;
        $Prices = $Order->calculateOrderPrices($OrderServices, $paymentType, $AppPercent);
        $Order->update([
            'price'                 => Arr::get($Prices, 'order_price'),
            'total_price'           => Arr::get($Prices, 'order_price') + Arr::get($Prices, 'bank_commission'),
            'bank_percent'          =>$paymentType == (new Order)->paymentTypes['balance'] ? (int) $this->getConfig('bank_percent') : 0,
            'bank_percent_amount'   => Arr::get($Prices, 'bank_commission'),
            'app_percent'           => $AppPercent,
            'app_percent_amount'    => Arr::get($Prices, 'app_commission'),
        ]);
        return Redirect::refresh();
    }
    public function formAfterSave(Order $Order)
    {
        if ($Order->status_id == $Order->statuses['user_payed']) {
            $AppPercent = (new Worker)->getWorkerCommission($Order->worker_id);
//            (new Notification)->sendTemplateNotifications([$Order->worker_id], 'userAcceptedOrder', [$User->name.' '.$User->surname], ['type' => 'order', 'id' => Arr::get($params, 'order_id')] ,'order_details');
            (new Worker)->updateBalance($Order->worker_id, $Order->getPriceToCharge($Order->total_price, $AppPercent), false);
            $client = User::find($Order->client_id);
            (new Message)->sendSystemMessage($Order->conversation_id, 'payed_with_cash', ['order_status_id' => $Order->statuses['user_payed']], [], $client->lang);
            (new User)->checkUserBusyStatus($Order->worker_id);
        }
        $Order->update([
            'seen'          => 0
        ]);
//
//        $request = \request()->all();
//        $OrderServices = $Order->OrderServices()->get()->toArray();
//        $paymentType = $Order->payment_type;
//        $Prices = $Order->calculateOrderPrices($OrderServices, $paymentType);
//        $Order->update([
//            'price'                 => Arr::get($Prices, 'order_price'),
//            'total_price'           => Arr::get($Prices, 'order_price') + Arr::get($Prices, 'bank_commission'),
//            'bank_percent'          =>$paymentType == (new Order)->paymentTypes['balance'] ? (int) $this->getConfig('bank_percent') : 0,
//            'bank_percent_amount'   => Arr::get($Prices, 'bank_commission'),
//        ]);
    }

    public function formExtendFields($host, $fields)
    {
        if ($host->model->status_id == 4) {
            $host->getFields()['total_price']->disabled = true;
        }
    }

    public function getAll(Request $request, Order $orderModel)
    {
        return $this->response($orderModel->getAll($request->all()));
    }

    public function getOrdersAndOffers(Request $request, Order $orderModel)
    {
        return $this->response($orderModel->getOrdersAndOffers($request->all()));
    }

    public function getOne(Request $request, Order $orderModel, $lang, $id = null)
    {
        $userModel = JWTAuth::authenticate($request->bearerToken());
        return $this->response($orderModel->getOne($id, $request->all()));
    }

//    public function export()
//    {
//        die('sadsa');
//    }

//    public function getOneByConversationID(Request $request, Order $orderModel)
//    {
//        return $this->response($orderModel->getAll($request->all()));
//    }

    public function createOrder(Request $request, Order $orderModel)
    {
        $validator = Validator::make($request->all(), [
            'worker_id'           => [
                'required',
                'integer',
                Rule::exists('users', 'id')
                ->where('user_type', (new User)->getUserTypeID('worker'))
            ],
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->getMessageBag(), self::$ERROR_CODES['VALIDATION_ERROR']);
        }
        return $this->response($orderModel->createOrder($request->all()));
    }

//    public function updateOrder(Request $request, Order $orderModel)
//    {
//        $validator = Validator::make($request->all(), [
//            'order_id'           => 'required|integer|exists:skillset_orders_,id',
//            'status_id'          => 'required|integer'
//        ]);
//        if ($validator->fails()) {
//            return $this->errorResponse($validator->getMessageBag());
//        }
//        return $this->response($orderModel->updateOrder($request->all()));
//    }
    public function finishOrderByWorker(Request $request, Order $orderModel)
    {
//        config()->set('auth.UserID', 20);
//        config()->set('auth.UserType', 1);
        $validator = Validator::make($request->all(), [
            'order_id'                   => 'required|integer|exists:skillset_orders_,id',
            'payment_type'               => 'required|integer|min:0|max:1',
            'services'                   => 'required|array',
            'services.*.title'           => 'required|string',
            'services.*.amount'          => 'required|integer',
            'services.*.unit_id'         => 'required|integer|exists:skillset_details_units,id',
            'services.*.unit_price'      => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->getMessageBag(), self::$ERROR_CODES['VALIDATION_ERROR']);
        }
        return $this->response($orderModel->finishOrderByWorker($request->all()));
    }

    public function finishOrderByUser(Request $request, Order $orderModel)
    {
        $validator = Validator::make($request->all(), [
            'order_id'                   => 'required|integer|exists:skillset_orders_,id'
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->getMessageBag(), self::$ERROR_CODES['VALIDATION_ERROR']);
        }
        return $this->response($orderModel->finishOrderByUser($request->all()));
    }

    public function userHasOrderUpdates(Order $orderModel)
    {
        return $this->response($orderModel->userHasOrderUpdates());
    }

    private function recalculatePriceOnEdit()
    {

    }
}
