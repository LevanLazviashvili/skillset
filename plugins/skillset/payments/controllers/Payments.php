<?php namespace skillset\Payments\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Cms\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use October\Rain\Support\Arr;
use RainLab\User\Models\User;
use RainLab\User\Models\Worker;
use skillset\Conversations\Models\Message;
use skillset\Jobs\Models\Job;
use skillset\Jobs\Models\Offer;
use skillset\Marketplace\Models\Application;
use skillset\Notifications\Models\Notification;
use skillset\orders\models\Advert;
use skillset\Orders\Models\FillBalanceOrder;
use skillset\Orders\Models\Order;
use skillset\Jobs\Models\Order as JobOrder;
use skillset\Marketplace\Models\Order as MarketplaceOrder;
use skillset\Marketplace\Models\Offer as MarketplaceOffer;
use skillset\Payments\Models\Payment;

class Payments extends Controller
{
    use ApiResponser;
    public $implement = [        'Backend\Behaviors\ListController'    ];
    
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('skillset.Payments', 'main-menu-item-payment');
    }

    public function paymentCallback(Request $request, Payment $paymentModel)
    {
        $Payment = $paymentModel->where('payment_hash', $request->input('payment_hash'))->first();

        if ($Payment) {

            $Payment->update([
                'status'                => $request->input('status'),
                'ipay_payment_id'       => $request->input('ipay_payment_id'),
                'status_description'    => $request->input('status_description'),
                'payment_method'        => $request->input('payment_method'),
                'card_type'             => $request->input('card_type'),
                'pan'                   => $request->input('pan'),
                'transaction_id'        => $request->input('transaction_id'),
                'pre_auth_status'       => $request->input('pre_auth_status'),
                'capture_method'        => $request->input('capture_method'),
            ]);

            if (($request->input('status') == 'success') && ($Payment->finished != 1)) {
                switch ($Payment->payment_type) {
                    case $paymentModel->paymentTypes['order'] :
                        $Order = (new Order)->where('payment_hash', $request->input('payment_hash'));
                        $OrderData = $Order->first();
                        $Order->update(['status_id' =>(new Order)->statuses['user_payed']]);
                        (new Message)->sendSystemMessage($OrderData->conversation_id, 'payed_with_balance', ['order_status_id' => (new Order)->statuses['user_payed']]);
                        $User = (new User)->find($OrderData->client_id);
                        (new Notification)->sendTemplateNotifications([$Order->worker_id], 'userPaidOrder', [$User->name.' '.$User->surname], ['type' => 'order', 'id' => $OrderData->id], 'order_details');

                        break;
                    case $paymentModel->paymentTypes['job_order'] :
                        $orderModel = new JobOrder();

                        $order = JobOrder::with('offer.job')
                            ->where('payment_hash', $request->input('payment_hash'))
                            ->first();

                        $order->offer->update([
                            'status' => (new Offer())->statuses['offer_accepted_by_client']
                        ]);

                        $order->offer->job()->update([
                            'status' => (new Job())->statuses['in_progress']
                        ]);

                        $order->update(['status' => $orderModel->statuses['work_started']]);

                        (new Message)->sendSystemMessage(
                            $order->offer->conversation_id,
                            'job_payed_with_balance',
                            ['order_status_id' => $orderModel->statuses['work_started']]
                        );

                        $worker = $orderModel->getUserByRole($order, 'worker');
                        $client = $orderModel->getUserByRole($order, 'client');

                        (new Notification)->sendTemplateNotifications(
                            [$worker->id],
                            'userPaidOrder',
                            [$client->name.' '.$client->surname],
                            ['type' => 'job_order', 'id' => $order->id],
                            'job_order_details'
                        );
                        break;
                    case $paymentModel->paymentTypes['marketplace_order'] :
                        $orderModel = new MarketplaceOrder();

                        $order = MarketplaceOrder::with('offer.application')
                            ->where('payment_hash', $request->input('payment_hash'))
                            ->first();

                        $order->update(['status' => $orderModel->statuses['client_paid']]);

                        $order->offer->update([
                            'status' => (new MarketplaceOffer())->statuses['offer_accepted']
                        ]);

                        $order->offer->application(['status' => (new Application())->statuses['processing']]);

                        (new Message)->sendSystemMessage(
                            $order->offer->conversation_id,
                            'marketplace_offer_payed',
                            ['offer_status_id' => $order->offer->status]
                        );

                        break;
                    case $paymentModel->paymentTypes['fill_balance']:
                        $Order = (new FillBalanceOrder)->where('payment_hash', $request->input('payment_hash'))->where('status_id', 0);
                        $OrderData = $Order->first();
                        $Order->update(['status_id' => 1]);
                        if ($OrderData) {
                            (new Worker)->updateBalance($Payment->user_id, $OrderData->amount);
                        }
                        break;
                    case $paymentModel->paymentTypes['vip_job']:
                        $job = Job::whereHas('adverts', function ($query) use ($Payment) {
                            $query->where('id', $Payment->order_id);
                        })->first();

                        if ($job){
                            $job->update([
                                'type' => $job->types['vip'],
                                'active' => true
                            ]);
                        }

                        break;
                    case $paymentModel->paymentTypes['vip_marketplace_app']:
                        $app = Application::whereHas('adverts', function ($query) use ($Payment) {
                            $query->where('id', $Payment->order_id);
                        })->first();

                        if ($app){
                            $app->update([
                                'type' => $app->types['vip'],
                                'active' => true
                            ]);
                        }

                        break;
                }

                $Payment->update(['finished' => 1]);
            }

        }

        return $this->successResponse([]);
    }

    public function refundCallback(Request $request)
    {
        return $this->successResponse([]);
    }

    public function getPaymentStatus(Request $request, Payment $paymentModel)
    {
        $validator = Validator::make($request->all(), [
            'order_id'      => 'required|integer|exists:skillset_orders_,id',
            'payment_type'  => 'required|integer',
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->getMessageBag(), self::$ERROR_CODES['VALIDATION_ERROR']);
        }
        return $this->response($paymentModel->getPaymentStatus($request->input('order_id'), $request->input('payment_type')));
    }

    public function paymentPage(Request $request, Payment $paymentModel)
    {
        if (!$request->input('order_id') || !$request->input('payment_type')) {
            return 'Order Not Found';
        }
        $Payment = $paymentModel->getPaymentStatus($request->input('order_id'), $request->input('payment_type'));
        return Arr::get($Payment, 'payment_status', 'Undefined');

    }
}
