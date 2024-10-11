<?php namespace RainLab\User\Controllers;

use Backend\Facades\Backend;
use Backend\Facades\BackendAuth;
use Cms\Traits\ApiResponser;
use App\Helpers\ApiHelper;
use Auth;
use Cms\Traits\PushNotifications;
use Illuminate\Http\Request;
use Hash;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Lang;
use Flash;
use RainLab\User\Models\Worker;
use rainlab\User\Rules\IDCard;
use Response;
use Redirect;
use BackendMenu;
use Backend\Classes\Controller;
use rainlab\User\Rules\IDNumber;
use skillset\Conversations\Models\Conversation;
use Skillset\Forum\Models\Post;
use skillset\Jobs\Models\Job;
use skillset\Marketplace\Models\Application;
use skillset\Notifications\Models\Notification;
use skillset\Payments\Models\Payment;
use System\Classes\SettingsManager;
use RainLab\User\Models\User;
use RainLab\User\Models\UserGroup;
use RainLab\User\Models\MailBlocker;
use RainLab\User\Models\Settings as UserSettings;
use Tymon\JWTAuth\Facades\JWTFactory;
use Vdomah\JWTAuth\Models\Settings;
use Tymon\JWTAuth\Facades\JWTAuth;
use Vdomah\JWTAuth\Models\Token;
use skillset\Jobs\Models\Order as JobOrder;


class Users extends Controller
{
    use ApiResponser;
    use PushNotifications;
    /**
     * @var array Extensions implemented by this controller.
     */
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\ImportExportController::class,
        \Backend\Behaviors\RelationController::class,
    ];

    /**
     * @var array `FormController` configuration.
     */
    public $formConfig = 'config_form.yaml';

    /**
     * @var array `ListController` configuration.
     */
    public $listConfig = 'config_list.yaml';

    public $importExportConfig = 'config_import_export.yaml';
    protected $exportFileName = 'users.csv';

    /**
     * @var array `RelationController` configuration, by extension.
     */
    public $relationConfig = 'config_relation.yaml';

    /**
     * @var array Permissions required to view this page.
     */
    public $requiredPermissions = ['rainlab.users.access_users'];

    /**
     * @var string HTML body tag class
     */
    public $bodyClass = 'compact-container';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

//        BackendMenu::setContext('RainLab.User', 'user', 'users');
        SettingsManager::setContext('RainLab.User', 'settings');
    }

    public function onSetPercent()
    {
        $params = \request()->all();
        if (!Arr::get($params, 'users')) {
            Flash::error("აირჩიეთ მომხმარებლები");
            return;
        }
        if (!Arr::get($params, 'percent')) {
            Flash::error("მიუთითეთ საკომისიოს პროცენტი");
            return;
        }

        (new User)->whereIn('id', Arr::get($params, 'users'))->update(['app_commission_percent' => Arr::get($params, 'percent')]);
        Flash::success("საკომისიო წარმატებით განახლდა");
        return Redirect::refresh();
    }

    public function onStartChat($id)
    {
        $params = \request()->all();
        $ConversationModel = new Conversation();
        $UserID = Arr::get($params, 'user_id');
        $Conversation = $ConversationModel->hasActiveSupportConverstion($UserID);
        if (!$Conversation) {
            $ConvID = $ConversationModel->startNewConversation([$UserID], 0,1);
        } else {
            $ConvID = $Conversation->id;
        }
        $url = Backend::url('/skillset/conversations/conversations/update/'.$ConvID);
        return redirect($url);

    }

    public function onActivateWorker($id)
    {
        $user = (new User)->find($id);
        if (!$user) {
            Flash::error("ვერ მოხერხდა მომხმარებლის აქტივაცია, სცადეთ თავიდან");
            return Redirect::refresh();
        }
        $user->update(['status_id' => 1]);
        (new Notification)->sendTemplateNotifications([$id], 'userActivated');
        Flash::success("მომხმარებელი დადასტურებულია");
        return Redirect::refresh();
    }
    public function onDeactivateUser($id)
    {
        $user = (new User)->find($id);
        if (!$user) {
            Flash::error("ვერ მოხერხდა მომხმარებლის წაშლა, სცადეთ თავიდან");
            return Redirect::refresh();
        }
        $user->status_id = -1;
        $user->username = $user->username.' (Deleted)';
        $user->surname = $user->surname.' (Deleted)';
        $user->save();
        Flash::success("მომხმარებელი წარმატებით წაიშალა");
        return redirect(Backend::url('rainlab/user/users'));
    }

    public function onDeleteWorker($id)
    {

        $user = (new User)->find($id);
        if (!$user || $user->status_id != 0) {
            Flash::error("ვერ მოხერხდა მომხმარებლის წაშლა, სცადეთ თავიდან");
            return Redirect::refresh();
        }
        (new Notification)->sendTemplateNotifications([$id], 'userDeleted');
        $user->forceDelete();
        Flash::success("მომხმარებელი წარმატებით წაიშალა");
        return redirect(Backend::url('rainlab/user/users'));
    }

    public function onAcceptUserUpdate($id)
    {
        $user = (new User)->find($id);
        if (!$user) {
            Flash::error("ვერ მოხერხდა ცვლილებების დადასტურება, სცადეთ თავიდან");
            return Redirect::refresh();
        }
        $user->status_id = 1;
        $user->updated_fields = '[]';
        $user->save();
        Flash::success("ცვლილებები დადასტურებულია");
        return Redirect::refresh();

    }

    public function index()
    {
        $this->addJs('/plugins/rainlab/user/assets/js/bulk-actions.js');

        $this->asExtension('ListController')->index();
    }

    /**
     * {@inheritDoc}
     */
//    public function listInjectRowClass($record, $definition = null)
//    {
//        $classes = [];
//
//        if ($record->trashed()) {
//            $classes[] = 'strike';
//        }
//
//        if ($record->isBanned()) {
//            $classes[] = 'negative';
//        }
//
//        if (!$record->is_activated) {
//            $classes[] = 'disabled';
//        }
//
//        if (count($classes) > 0) {
//            return join(' ', $classes);
//        }
//    }
//
    public function listExtendQuery($query)
    {
        $query->where('admin_user_id', null)->where('status_id', '>=', 0);
    }
//
//    public function formExtendQuery($query)
//    {
//        $query->withTrashed();
//    }

    /**
     * Display username field if settings permit
     */
//    public function formExtendFields($form)
//    {
//        /*
//         * Show the username field if it is configured for use
//         */
//        if (
//            UserSettings::get('login_attribute') == UserSettings::LOGIN_USERNAME &&
//            array_key_exists('username', $form->getFields())
//        ) {
//            $form->getField('username')->hidden = false;
//        }
//    }
//


    public function formAfterUpdate($model)
    {
        if ($model->is_busy == 0) {
            $model->update(['forced_busy' => 0]);
        }
    }

    public function formBeforeSave($model)
    {
        if (!Arr::get($_POST, 'User.app_commission_percent')) {
            $model->app_commission_percent = null;
            $model->save();
            unset($_POST['User']['app_commission_percent']);
        }
    }
//
//    public function formExtendModel($model)
//    {
//        $model->block_mail = MailBlocker::isBlockAll($model);
//
//        $model->bindEvent('model.saveInternal', function() use ($model) {
//            unset($model->attributes['block_mail']);
//        });
//    }
//
//    /**
//     * Manually activate a user
//     */
//    public function preview_onActivate($recordId = null)
//    {
//        $model = $this->formFindModelObject($recordId);
//
//        $model->attemptActivation($model->activation_code);
//
//        Flash::success(Lang::get('rainlab.user::lang.users.activated_success'));
//
//        if ($redirect = $this->makeRedirect('update-close', $model)) {
//            return $redirect;
//        }
//    }
//
//    /**
//     * Manually unban a user
//     */
//    public function preview_onUnban($recordId = null)
//    {
//        $model = $this->formFindModelObject($recordId);
//
//        $model->unban();
//
//        Flash::success(Lang::get('rainlab.user::lang.users.unbanned_success'));
//
//        if ($redirect = $this->makeRedirect('update-close', $model)) {
//            return $redirect;
//        }
//    }
//
//    /**
//     * Display the convert to registered user popup
//     */
//    public function preview_onLoadConvertGuestForm($recordId)
//    {
//        $this->vars['groups'] = UserGroup::where('code', '!=', UserGroup::GROUP_GUEST)->get();
//
//        return $this->makePartial('convert_guest_form');
//    }
//
//    /**
//     * Manually convert a guest user to a registered one
//     */
//    public function preview_onConvertGuest($recordId)
//    {
//        $model = $this->formFindModelObject($recordId);
//
//        // Convert user and send notification
//        $model->convertToRegistered(post('send_registration_notification', false));
//
//        // Remove user from guest group
//        if ($group = UserGroup::getGuestGroup()) {
//            $model->groups()->remove($group);
//        }
//
//        // Add user to new group
//        if (
//            ($groupId = post('new_group')) &&
//            ($group = UserGroup::find($groupId))
//        ) {
//            $model->groups()->add($group);
//        }
//
//        Flash::success(Lang::get('rainlab.user::lang.users.convert_guest_success'));
//
//        if ($redirect = $this->makeRedirect('update-close', $model)) {
//            return $redirect;
//        }
//    }
//
//    /**
//     * Impersonate this user
//     */
//    public function preview_onImpersonateUser($recordId)
//    {
//        if (!$this->user->hasAccess('rainlab.users.impersonate_user')) {
//            return Response::make(Lang::get('backend::lang.page.access_denied.label'), 403);
//        }
//
//        $model = $this->formFindModelObject($recordId);
//
//        Auth::impersonate($model);
//
//        Flash::success(Lang::get('rainlab.user::lang.users.impersonate_success'));
//    }
//
//    /**
//     * Unsuspend this user
//     */
//    public function preview_onUnsuspendUser($recordId)
//    {
//        $model = $this->formFindModelObject($recordId);
//
//        $model->unsuspend();
//
//        Flash::success(Lang::get('rainlab.user::lang.users.unsuspend_success'));
//
//        return Redirect::refresh();
//    }

    /**
     * Force delete a user.
     */
//    public function update_onDelete($recordId = null)
//    {
//        $model = $this->formFindModelObject($recordId);
//
//        $model->forceDelete();
//
//        Flash::success(Lang::get('backend::lang.form.delete_success'));
//
//        if ($redirect = $this->makeRedirect('delete', $model)) {
//            return $redirect;
//        }
//    }

    /**
     * Perform bulk action on selected users
     */
//    public function index_onBulkAction()
//    {
//        if (
//            ($bulkAction = post('action')) &&
//            ($checkedIds = post('checked')) &&
//            is_array($checkedIds) &&
//            count($checkedIds)
//        ) {
//
//            foreach ($checkedIds as $userId) {
//                if (!$user = User::withTrashed()->find($userId)) {
//                    continue;
//                }
//
//                switch ($bulkAction) {
//                    case 'delete':
//                        $user->forceDelete();
//                        break;
//
//                    case 'activate':
//                        $user->attemptActivation($user->activation_code);
//                        break;
//
//                    case 'deactivate':
//                        $user->delete();
//                        break;
//
//                    case 'restore':
//                        $user->restore();
//                        break;
//
//                    case 'ban':
//                        $user->ban();
//                        break;
//
//                    case 'unban':
//                        $user->unban();
//                        break;
//                }
//            }
//
//            Flash::success(Lang::get('rainlab.user::lang.users.'.$bulkAction.'_selected_success'));
//        }
//        else {
//            Flash::error(Lang::get('rainlab.user::lang.users.'.$bulkAction.'_selected_empty'));
//        }
//
//        return $this->listRefresh();
//    }

    public function onBulkAction()
    {
        return true;
    }
    public function signUp(Request  $request, User $userModel)
    {
        $rules = [
            'username'      => (new User)->getUserNameValidationRules( true, true),
            'code'          => 'required',
            'token'         => 'required',
            'user_type'     => 'required|integer|between:0,1',
            'name'          => 'required|min:2|max:20',
            'surname'       => 'required|min:3|max:40',
            'legal_entity'  => 'required|integer|between:0,1',
            'avatar'        => 'nullable|image|max:20000',
        ];


        if ($request->input('legal_entity')) {
            $rules = array_merge($rules, [
                'org_legal_type_id' => 'required',
                'org_title'         => 'required',
                'org_id_number'     => ['required', new IDNumber()],
                'id_number'         => ['required', new IDNumber()],
            ]);
        }
        if ($request->input('user_type') == 1) {
            $rules = array_merge($rules, [
                'country_id'    => 'required|exists:skillset_details_countries,id',
                'region_id'     => 'required|exists:skillset_details_regions,id',
                'address'       => 'required|min:2',
                'id_card'       => 'required|array|min:2|max:2',
//                'id_card'       => 'array|min:2|max:2',
                'id_card.*'     => 'image|max:20000'
            ]);
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->errorResponse($validator->getMessageBag(), self::$ERROR_CODES['VALIDATION_ERROR']);
        }
        if (!$userModel->checkVerficiationCode($request->all())) {
            return $this->errorResponse('invalid_verification_token', self::$ERROR_CODES['VERIFICATION_ERROR']);
        }

        if (!$Response = $userModel->signUp($request)) {
            return $this->errorResponse('Auth Error', self::$ERROR_CODES['AUTH_ERROR']);
        }

        return $this->response($Response);
    }

    public function login(Request $request, User $userModel){
        if (!$Response = $userModel->login($request)) {
            return $this->errorResponse('invalid_credentials', self::$ERROR_CODES['AUTH_ERROR']);
        }
        return $this->response($Response);
    }

    public function getUserInfo(Request $request, User $userModel, $lang = null, $id = null)
    {
        if (!$Response = $userModel->getInfo($request, $id)) {
            return $this->errorResponse('Not Found', self::$ERROR_CODES['NOT_FOUND']);
        }

        return $this->response($Response);
    }

    public function edit(Request $request, User $userModel) {
        $rules = [
            'email'         => 'email',
            'name'          => 'min:2|max:20',
            'surname'       => 'min:2|max:40',
            'id_number'     => 'min:10|max:15',
            'phone_number'  => 'min:9|max:20',
            'country_id'    => 'exists:skillset_details_countries,id|nullable',
            'region_id'     => 'exists:skillset_details_regions,id|nullable',
            'address'       => 'min:2',

            'username'      => $userModel->getUserNameValidationRules(true, false)
        ];

        if ($request->input('password')) {
            $rules = array_merge($rules, [
                'old_password' => 'required',
                'password' => 'required:create|between:8,255|confirmed',
                'password_confirmation' => 'required_with:password|between:8,255',
            ]);
        }

        if ($request->file('avatar')) {
            $rules = array_merge($rules, [
                'avatar'        => 'nullable|image|max:20000',
            ]);
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            traceLog($validator->getMessageBag()->toArray());
            return $this->errorResponse($validator->getMessageBag()->toArray(), self::$ERROR_CODES['VALIDATION_ERROR']);
        }

        $User = $userModel->find(config('auth.UserID'));
        if ($request->input('username') && $User->username != $request->input('username')) {
            if ($request->input('code')) {
                $userModel->checkVerficiationCode($request->all());
            } else {
                $Data = $userModel->sendVerificationCode($request->all());
                return $this->errorResponse('needs_verification', User::$ERROR_CODES['NEEDS_VERIFICATION'], $Data);
            }

        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->errorResponse($validator->getMessageBag(), self::$ERROR_CODES['VALIDATION_ERROR']);
        }

        return $this->response($userModel->edit($request));


    }

    public function changePassword(Request $request, User $userModel) {
        $rules = [
            'old_password' => 'required',
            'password' => 'required:create|between:8,255|confirmed',
            'password_confirmation' => 'required_with:password|between:8,255',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->errorResponse($validator->getMessageBag()->toArray(), self::$ERROR_CODES['VALIDATION_ERROR']);
        }
        $this->response($userModel->changePassword($request));

    }

    public function updateGallery (Request $request, User $userModel) {
        $validator = Validator::make($request->all(), ['gallery.*' => 'image|max:10000']);
        if ($validator->fails()) {
            return $this->errorResponse($validator->getMessageBag()->toArray(), self::$ERROR_CODES['VALIDATION_ERROR']);
        }
        return $this->response($userModel->updateGallery($request));
    }

    public function getGallery(Request $request, User $userModel) {
        return $this->response($userModel->getGallery($request));
    }

    public function changeStatus(Request $request, User $userModel) {
        $validator = Validator::make($request->all(), ['is_busy' => 'required|integer|between:0,1']);
        if ($validator->fails()) {
            return $this->errorResponse($validator->getMessageBag()->toArray(), self::$ERROR_CODES['VALIDATION_ERROR']);
        }
        return $this->successResponse($userModel->changeStatus($request));
    }

    public function sendVerificationCode(Request $request, Token $token, User $userModel)
    {
        $validator = Validator::make($request->all(), [
            'username' => $userModel->getUserNameValidationRules( true)
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->getMessageBag()->toArray(), self::$ERROR_CODES['VALIDATION_ERROR']);
        }
        return $this->response($userModel->sendVerificationCode($request->all()));
    }

    public function checkVerificationCode(Request $request, Token $token, User $userModel)
    {
        $validator = Validator::make($request->all(), [
            'username' => $userModel->getUserNameValidationRules(true),
            'code'     => 'required'
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->getMessageBag()->toArray(), self::$ERROR_CODES['VALIDATION_ERROR']);
        }
        return $this->response($userModel->checkVerficiationCode($request->all()));
    }

    public function refreshToken(Request $request)
    {
        $token = $request->input('token');

        try {
            // attempt to refresh the JWT
            if (!$token = JWTAuth::refresh($token)) {
                return response()->json(['error' => 'could_not_refresh_token'], 401);
            }
        } catch (Exception $e) {
            // something went wrong
            return response()->json(['error' => 'could_not_refresh_token'], 500);
        }

        // if no errors are encountered we can return a new JWT
        return $this->response(compact('token'));
    }

    public function logout(Request $request)
    {
        $token = config('auth.Token');
        $User = JWTAuth::authenticate($request->bearerToken());

        $User->device_token = null;
        $User->save();
        try {
            JWTAuth::invalidate($token);
        } catch (Exception $e) {
            return response()->json(['error' => 'could_not_invalidate_token'], 500);
        }
        return $this->response('token_invalidated');
    }

    public function sendResetPasswordCode(Request $request, User $userModel)
    {
        $validator = Validator::make($request->all(), [
            'username' => $userModel->getUserNameValidationRules(false)
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->getMessageBag()->toArray(), self::$ERROR_CODES['VALIDATION_ERROR']);
        }
        return $this->successResponse($userModel->sendResetPasswordCode($request->all()));
    }

    public function resetPassword(Request $request, User $userModel)
    {
        $validator = Validator::make($request->all(), [
            'username'              => $userModel->getUserNameValidationRules(false),
            'code'                  => 'required',
            'password'              => 'required:create|between:8,255|confirmed',
            'password_confirmation' => 'required_with:password|between:8,255',
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->getMessageBag()->toArray(), self::$ERROR_CODES['VALIDATION_ERROR']);
        }
        return $this->response($userModel->resetPassword($request->all()));
    }

    public function saveDeviceToken(Request $request, User $userModel)
    {
        $validator = Validator::make($request->all(), [
            'device_token'  => 'required',
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->getMessageBag()->toArray(), self::$ERROR_CODES['VALIDATION_ERROR']);
        }
        $userModel->where('id', config('auth.UserID'))->update([
            'device_token'  => $request->input('device_token')
        ]);
        return $this->successResponse([]);
    }

//    public function sendTestPushNotification(Request $request)
//    {
//        $this->SendPushNotification($request->input('user_id'), $request->input('title'), $request->input('body'),
//            $request->input('icon_state'), $request->input('button_title'), $request->input('action_page'),
//            $request->input('action_params'), $request->input('show_in_app'));
//    }

    public function fillBalance(Request $request)
    {
        $validator = Validator::make(array_merge($request->all()), [
            'amount'    => 'required|numeric|min:0.01|max:999999'
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->getMessageBag()->toArray(), self::$ERROR_CODES['VALIDATION_ERROR']);
        }

        return $this->response((new Payment)->fillBalance(config('auth.UserID'), $request->amount));
    }

    public function updateUserLang(Request $request, User $userModel)
    {
        $validator = Validator::make(array_merge($request->all()), [
            'lang'    => 'required'
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->getMessageBag()->toArray(), self::$ERROR_CODES['VALIDATION_ERROR']);
        }
        return $this->response($userModel->updateUserLang($request));
    }

    public function deleteMyUser(Request $request, User $userModel)
    {
        $validator = Validator::make(array_merge($request->all()), [
            'password'    => 'required'
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->getMessageBag()->toArray(), self::$ERROR_CODES['VALIDATION_ERROR']);
        }
        $userModel->deleteMyUser($request);
        return $this->successResponse([]);
    }

    public function onRelationButtonDelete()
    {
        $params = \request()->all();
        $user = BackendAuth::getUser();
        unset($params['_session_key']);
        unset($params['_token']);
        traceLog('Admin Delete SubServices: '.json_encode([
            'AdminID' => $user->id,
            'Params'  => $params], JSON_UNESCAPED_UNICODE));
        return parent::onRelationButtonDelete();
    }

    public function makeJobUsersWithNegativeBalanceInactive()
    {
        $orderModel = new JobOrder();

        $start = now()->subDay()->subMinutes(15)->toDateTimeString();
        $end = now()->subDay()->toDateTimeString();

        $orders = $orderModel->with('offer.job')
            ->where('status', $orderModel->statuses['paid'])
            ->where('completed_at', '>=', $start)
            ->where('completed_at', '<=', $end)
            ->get();

        $userIds = [];

        foreach ($orders as $order) {
            $userIds[] = $order->offer->job->user_id;
            $userIds[] = $order->offer->author_id;
        }

        $userIds = array_unique($userIds);

        (new User)->whereIn('id', $userIds)->where('balance', '<', 0)->update(['is_unactive' => 1]);
    }

    public function getUnreadCounts()
    {
        $user = User::find(config('auth.UserID'));

        $newJobCount = Job::publicVisible()
            ->where('created_at', '>=', $user->created_at)
            ->when($user->last_seen_jobs, function ($query) use ($user) {
                $query->where('created_at', '>=', $user->last_seen_jobs);
            })
            ->when($user->region_id, function ($query, $regionId) {
                $query->where(function ($query) use ($regionId) {
                    $query->where('region_id', $regionId)
                        ->orWhere('region_id', null);
                });
            })
            ->count();

        $newProductCount = Application::publicVisible()
            ->where('created_at', '>=', $user->created_at)
            ->when($user->last_seen_marketplace, function ($query) use ($user) {
                $query->where('created_at', '>=', $user->last_seen_marketplace);
            })
            ->when($user->region_id, function ($query, $regionId) {
                $query->where(function ($query) use ($regionId) {
                    $query->where('region_id', $regionId)
                        ->orWhere('region_id', null);
                });
            })
            ->count();

        $newPostCount = Post::where('created_at', '>=', $user->created_at)
            ->when($user->last_seen_forum, function ($query) use ($user) {
                $query->where('created_at', '>=', $user->last_seen_forum);
            })
            ->count();

        return $this->response([
            'jobs' => $newJobCount,
            'products' => $newProductCount,
            'posts' => $newPostCount
        ]);
    }

    public function updateBankAccountNumber(Request $request)
    {
        $user = User::find(config('auth.UserID'));

        $validator = Validator::make($request->all(), [
            'bank_account_number' => 'nullable|string|max:50'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->getMessageBag(), self::$ERROR_CODES['VALIDATION_ERROR']);
        }

        $user->update(['bank_account_number' => $request->bank_account_number]);

        return $this->successResponse();
    }
}
