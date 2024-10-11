<?php namespace skillset\Offers\Controllers;

use Aws\Api\Service;
use Backend\Classes\Controller;
use Backend\Facades\Backend;
use BackendMenu;
use Cms\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RainLab\User\Models\User;
use RainLab\User\Models\Worker;
use skillset\Conversations\Models\Conversation;
use skillset\Conversations\Models\Message;
use skillset\Offers\Models\Offer;
use skillset\Offers\Models\OfferWorker;
use skillset\Offers\Rules\ArrayOfWorkers;
use Flash;
use skillset\Orders\Models\OrderServiceTmp;

class Offers extends Controller
{
    use ApiResponser;
    public $implement = [        'Backend\Behaviors\ListController', 'Backend\Behaviors\FormController'  ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();
    }

    public function listExtendQuery($query)
    {
//        $query->where('client_id', 0);
    }

    public function onOfferByAdmin()
    {
        config()->set('auth.UserID', 0);
        $request = \request()->all();
        if (!Arr::get($request, 'Offer.custom_client_phone') or !Arr::get($request, 'Offer.custom_client_address') or !Arr::get($request, 'Offer.offer')) {
            Flash::error('გთხოვთ შეავსოთ ყველა აუციელებელი ველი');
            return;
        }
        $params = [
            'service_sub_ids'       => Arr::get($request, 'Offer.SubServices'),
            'service_ids'           => Arr::get($request, 'Offer.Services'),
            'price_from'            => Arr::get($request, 'Offer.price_from'),
            'price_to'              => Arr::get($request, 'Offer.price_to'),
            'region_ids'            => Arr::get($request, 'Offer.region'),
            'offer'                 => Arr::get($request, 'Offer.offer'),
            'custom_client_phone'   => Arr::get($request, 'Offer.custom_client_phone'),
            'custom_client_address' => Arr::get($request, 'Offer.custom_client_address'),
            'workers'               => Arr::get($request, 'workers'),
            'comment'               => Arr::get($request, 'Offer.comment')
        ];
        if (!Arr::get($params, 'workers') || empty(Arr::get($params, 'workers'))) {
            $params['workers'] = (new Worker)->getAll($params, true);
            if (empty($params['workers'])) {
                return $this->errorResponse('not found workers', self::$ERROR_CODES['NOT_FOUND']);
            }
        }
        $params['workers'] = is_array(Arr::get($params, 'workers')) ? Arr::get($params, 'workers') : explode('.', Arr::get($params, 'workers'));
        $params['workers'] = (new Worker)->checkWorkerIDs($params['workers']);
        if (count($params['workers']) == 0) {
            return $this->errorResponse('Unfortunately there are not free workers');
        }
        $Offer = (new Offer)->store($params);
        (new OfferWorker)->store(array_merge(['offer_id' => $Offer->id], $params));

        $model = $this->formCreateModelObject();
        Flash::success("შეკვეთა გაგზავნილია");

        if ($redirect = $this->makeRedirect('create', $model)) {
            return $redirect;
        }

    }

    public function onStartChat($id)
    {
        $params = \request()->all();
//        $ConversationModel = new Conversation();
        $OfferWorkerID = Arr::get($params, 'id');
        $OfferedWorker = (new OfferWorker)->with('Offer')->find($OfferWorkerID);
        $ConversationID = $OfferedWorker->conversation_id;
//        $Conversation = $ConversationModel->hasActiveSupportConverstion($WorkerID);
        if (!$ConversationID) {
            $ConversationID = (new OfferWorker)->startConversation($OfferedWorker);
        }
        $url = Backend::url('/skillset/conversations/conversations/update/'.$ConversationID);
        return redirect($url);

    }

    public function formExtendFields($host, $fields)
    {
//        die('sdsa');
    }


    public function update($recordId = null, $context = null)
    {
        $Data = (new Offer)->getOfferEditData(['offer_id' => $recordId]);
        $this->vars = array_merge($this->vars, $Data);
        parent::update($recordId, $context);
    }

    public function adminGetOfferWorkers(Request $request)
    {
        if ($request->get('secret') != config('app.admin_secret')) {
            return $this->errorResponse();
        }
        $Data = (new Offer)->getOfferEditData(['offer_id' => $request->get('offer_id')]);
        return $this->response($Data);

    }

    public function onCancelOffer($id)
    {
        $params = \request()->all();
        $OfferWorkerModel = (new OfferWorker);
        $OfferWorkerModel->updateOffer([
            'offer_id'  => $id,
            'worker_id' => Arr::get($params, 'worker_id'),
            'status_id' => $OfferWorkerModel->statuses['offer_rejected_by_client']
        ], true);
        $model = $this->formCreateModelObject();
        Flash::success("შეთავაზება გაუქმდა");

        if ($redirect = $this->makeRedirect('create', $model)) {
            return $redirect;
        }
    }

    public function onAcceptOffer($id)
    {
        $params = \request()->all();
        $OfferWorkerModel = (new OfferWorker);
        $OfferWorkerModel->updateOffer([
            'offer_id'              => $id,
            'worker_id'             => Arr::get($params, 'worker_id'),
            'status_id'             => $OfferWorkerModel->statuses['offer_accepted_by_client'],
            'custom_client_phone'   => Arr::get($params, 'Offer.custom_client_phone'),
            'custom_client_address' => Arr::get($params, 'Offer.custom_client_address')
        ], true);
        $model = $this->formCreateModelObject();
        Flash::success("შეთავაზება მიღებულია");

        if ($redirect = $this->makeRedirect('create', $model)) {
            return $redirect;
        }
    }

    public function getAll(Request $request, Offer $offerModel)
    {
        return $this->response($offerModel->getAll($request->all()));
    }

    public function getOne(Offer $offerModel, $lang, $id)
    {
        return $this->response($offerModel->getOne($id));
    }

    public function makeOffer(Request $request, Offer $offerModel, OfferWorker $offerWorkerModel, Worker $workerModel)
    {
        $params = $request->all();
        $rules = [
            'offer'              => 'required',
            'workers'            => 'required',
            'service_id'         => 'required_without:service_sub_ids',
            'service_sub_ids'    => 'required_without:service_id'
        ];
        if (!Arr::get($params, 'workers')) {
            $params['workers'] = $workerModel->getAll($request->all(), true);
            if (empty($params['workers'])) {
                return $this->errorResponse('not found workers', self::$ERROR_CODES['NOT_FOUND']);
            }
        }
        $params['workers'] = is_array(Arr::get($params, 'workers')) ? Arr::get($params, 'workers') : explode('.', Arr::get($params, 'workers'));
        $params['workers'] = (new Worker)->checkWorkerIDs($params['workers']);
        if (count($params['workers']) == 0) {
            return $this->errorResponse('Unfortunately there are not free workers');
        }
        $validator = Validator::make($params, $rules);
        if ($validator->fails()) {
            return $this->errorResponse($validator->getMessageBag(), self::$ERROR_CODES['VALIDATION_ERROR']);
        }
        $Offer = $offerModel->store($params);
        $offerWorkerModel->store(array_merge(['offer_id' => $Offer->id], $params));
        return $this->response([
            'offer_id'                  => $Offer->id,
            'offered_workers_count'     => count($params['workers'])
        ]);
    }

    public function updateOffer(Request $request, OfferWorker $offerWorkerModel)
    {
//        config()->set('auth.UserID', 20);
//        config()->set('auth.UserType', 1);
        $params = [
            'offer_id'           => 'required|integer|exists:skillset_offers_,id',
            'status_id'          => 'required|integer'
        ];
        if ((new User)->getUserType() == 'client') {
            $params['worker_id'] = 'required|integer|exists:users,id';
        }
        if ($request->input('status_id') == $offerWorkerModel->statuses['offer_accepted_by_worker']) {
            $params = array_merge($params, [
                'end_date'                   => 'required|date|date_format:Y-m-d|after:yesterday',
//                'services'                   => 'required|array',
                'services'                   => 'array',
                'services.*.title'           => 'required|string',
                'services.*.amount'          => 'required|integer',
                'services.*.unit_id'         => 'required|integer|exists:skillset_details_units,id',
                'services.*.unit_price'      => 'required|numeric',
            ]);
//            $params['end_date'] = 'required|date|date_format:Y-m-d|after:yesterday';
        }
        $validator = Validator::make($request->all(), $params);
        if ($validator->fails()) {
            return $this->errorResponse($validator->getMessageBag(), self::$ERROR_CODES['VALIDATION_ERROR']);
        }
        return $this->response($offerWorkerModel->updateOffer($request->all()));
    }

    public function getOfferServices(Request $request, OrderServiceTmp $orderServiceTmp)
    {
        $validator = Validator::make($request->all(), [
            'offer_id'          => 'required_without:order_id',
            'order_id'          => 'required_without:offer_id'
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->getMessageBag(), self::$ERROR_CODES['VALIDATION_ERROR']);
        }
        return $this->response($orderServiceTmp->getAll($request->all()));

    }

    public function editOfferServices(Request $request, OfferWorker $offerWorkerModel)
    {
        $validator = Validator::make($request->all(), [
            'offer_id'                   => 'required|integer|exists:skillset_offers_,id',
            'services.*.title'           => 'required|string',
            'services.*.amount'          => 'required|integer',
            'services.*.unit_id'         => 'required|integer|exists:skillset_details_units,id',
            'services.*.unit_price'      => 'required|numeric',
            'end_date'                   => 'date|date_format:Y-m-d|after:yesterday',
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->getMessageBag(), self::$ERROR_CODES['VALIDATION_ERROR']);
        }
        return $this->successResponse($offerWorkerModel->editOfferServices($request->all()));

    }

}
