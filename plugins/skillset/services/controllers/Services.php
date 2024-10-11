<?php namespace skillset\Services\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Cms\Traits\ApiResponser;
use RainLab\User\Models\User;
use skillset\Configuration\Traits\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use skillset\Services\Models\Service;
use RainLab\Translate\Classes\Translator;
use skillset\Services\Models\ServiceToUser;
use skillset\Services\Models\SubService;
use Response;

class Services extends Controller
{
    use ApiResponser;
    use Config;
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController',        'Backend\Behaviors\ReorderController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('skillset.Services', 'main-menu-item');
    }


    public function getAll(Service $serviceModel, $lang, Request $request)
    {
        return $serviceModel->getAll($request->all());
    }

    public function getServicesAndSubServices(
        Service $serviceModel,
        SubService $subServiceModel,
        User $userModel,
        Request $request
    )
    {
        return $this->response([
            'services' => $serviceModel->getAll($request->all()),
            'sub_services' => $subServiceModel->getAll($request->all()),
            'users' => $userModel->getAll($request->all())
        ]);

    }


    /**
     * Method adds or updates Users's sub services
     * if method gets sub_service_title, it registers new service and adds params to it
     *
     * @param Request $request
     * amount
     * unit_id
     * price_from
     * price_to
     *
     * service_sub_id
     * or:
     * service_id
     * sub_service_title
     */

    public function addSubServiceToUser(Request $request, ServiceToUser $serviceToUserModel, SubService $subServiceModel)
    {
        /** TODO service_sub_id filter by default = 1 */
        $Response = $this->ValidateSubServices($request->all());
        if ($Response !== true) {
            return $this->errorResponse($Response, self::$ERROR_CODES['VALIDATION_ERROR']);
        }
        $serviceToUserModel->updateUserSubServices($request->input('sub_services'), $request->input('service_id'));

        return $this->successResponse([]);
    }

    private function ValidateSubServices($params)
    {
        $subServiceModel = new SubService();
        $customSubServices = 0;
        $rules = [
            'sub_services'               => 'present|array',
            'sub_services.*.amount'      => 'required|integer',
            'sub_services.*.unit_id'     => 'required|integer|exists:skillset_details_units,id',
            'sub_services.*.price_from'  => 'required|numeric|min:0.01',
            'sub_services.*.price_to'    => 'required|numeric|min:0.02',
            'service_id'                 => 'required|integer|exists:skillset_services_,id'
        ];
        $validator = Validator::make($params, $rules);
        if ($validator->fails()) {
            return $validator->getMessageBag();
        }

        foreach (Arr::get($params, 'sub_services') AS $subService) {
            if (Arr::get($subService, 'sub_service_title')) {
                $rules = [
                    'sub_service_title' => 'required|min:3|max:40',
                ];
                $customSubServices++;
            } else {
                $rules = ['service_sub_id' => 'required|integer|exists:skillset_services_sub,id,service_id,'.Arr::get($params, 'service_id')];
            }
            if (Arr::get($subService, 'price_to') < Arr::get($subService, 'price_from')) {
                return 'price to should be greater than price from';
            }
            $validator = Validator::make($subService, $rules);
            if ($validator->fails()) {
                return $validator->getMessageBag();
            }
        }

        /* TODO should make some limits on custom sub services */
//        $UsersSubServices = $subServiceModel->where('skillset_services_sub.user_id', config('auth.UserID'))
//            ->join('skillset_services_sub_to_user AS sstu', function($q){
//                $q->on('sstu.services_sub_id', '=', 'skillset_services_sub.id');
//                $q->where('sstu.status_id', config('app.statuses.active'));
//            })->count();
//
//        if ($UsersSubServices + $customSubServices >= config('app.services.max_sub_services_per_user')) {
//            return ['status_message' => 'too_many_sub_services'];
//        }

        return true;
    }

//    public function add

    /**
     * Returns User's services, sub services and sub service params
     * gets user_id as param, if user_id is empty, returns authed user's services
     * @param Request $request
     * @return array
     */
    public function getUserServices(Request $request, ServiceToUser $serviceToUserModel)
    {
        return $serviceToUserModel->getUserServices($request->input('user_id') ?: config('auth.UserID'));
    }

    public function removeUserSubService(Request $request, $lang, $id, ServiceToUser $serviceToUserModel)
    {
        traceLog('removeUserSubService: '.json_encode([
                'UserID' => config('auth.UserID'),
                'params' => $request->all()
            ], JSON_UNESCAPED_UNICODE));
//        return $this->response($serviceToUserModel->where('user_id', config('auth.UserID'))->where('id', $id)->delete());
    }

}
