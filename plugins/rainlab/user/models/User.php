<?php namespace RainLab\User\Models;

use Cms\Traits\ApiResponser;
use http\Exception\RuntimeException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use League\Flysystem\Exception;
use October\Rain\Exception\ValidationException;
use skillset\details\Models\Country;
use skillset\details\Models\LegalType;
use skillset\details\Models\Region;
use skillset\Log\Models\EmailLog;
use skillset\Offers\Models\Offer;
use skillset\Offers\Models\OfferWorker;
use skillset\Orders\Models\Order;
use skillset\Payments\Models\Payment;
use skillset\Rating\Models\Rating;
use skillset\Services\Models\Service;
use skillset\Services\Models\ServiceToUser;
use skillset\Services\Models\SubService;
use Str;
use Auth;
use Mail;
use Event;
use Config;
use Carbon\Carbon;
use October\Rain\Auth\Models\User as UserBase;
use RainLab\User\Models\Settings as UserSettings;
use October\Rain\Auth\AuthException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;
use Cms\Traits\SmsOffice;
use skillset\Configuration\Traits\Config as AppConfig;
use rainlab\User\Rules\UniqPhone;
use skillset\Jobs\Models\Order as JobOrder;
use skillset\Marketplace\Models\Order as MarketplaceOrder;

class User extends UserBase
{
    use \October\Rain\Database\Traits\SoftDelete;
    use SmsOffice;
    use AppConfig;
    use ApiResponser;

    /**
     * @var string The database table used by the model.
     */
    protected $table = 'users';

    /**
     * Validation rules
     */
    public $rules = [
        'avatar'   => 'nullable|image|max:20000',
        'gallery.*' => 'image|max:10000',
        'password' => 'required:create|between:8,255|confirmed',
        'password_confirmation' => 'required_with:password|between:8,255',
    ];

    /**
     * @var array Relations
     */

    public $attachOne = [
        'avatar' => \System\Models\File::class
    ];

    public $attachMany = [
        'gallery'   =>   \System\Models\File::class,
        'id_card'   =>   \System\Models\File::class
    ];

    /**
     * @var array The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'surname',
        'login',
        'username',
        'password',
        'password_confirmation',
        'created_ip_address',
        'last_ip_address',
        'id_number',
        'legal_entity',
        'org_id_number',
        'org_legal_type_id',
        'org_title',
        'country_id',
        'region_id',
        'address',
        'user_type',
        'description',
        'is_busy',
        'avatar',
        'admin_user_id',
        'forced_busy',
        'is_unactive',
        'commission_free_till',
        'app_commission_percent',
        'status_id',
        'updated_fields',
        'is_certified',
        'last_seen_jobs',
        'last_seen_marketplace',
        'last_seen_forum',
        'bank_account_number',
        'turn_off_notifications'
    ];

    public $publicInfo = [
        'id',
        'name',
        'surname',
        'username',
        'legal_entity',
        'org_title',
        'country_id',
        'region_id',
        'user_type',
        'description',
        'is_busy',
        'rate',
        'rates_count',
        'is_unactive',
        'org_legal_type_id',
        'status_id',
        'is_certified'
    ];

    public $privateInfo = [
        'id',
        'name',
        'surname',
        'username',
        'id_number',
        'legal_entity',
        'org_id_number',
        'org_legal_type_id',
        'org_title',
        'country_id',
        'region_id',
        'address',
        'user_type',
        'description',
        'is_busy',
        'rate',
        'rates_count',
        'balance',
        'forced_busy',
        'is_unactive',
        'status_id',
        'is_certified',
        'bank_account_number',
        'turn_off_notifications'
    ];

    /**
     * Reset guarded fields, because we use $fillable instead.
     * @var array The attributes that aren't mass assignable.
     */
    protected $guarded = ['*'];

    /**
     * Purge attributes from data set.
     */
    protected $purgeable = ['password_confirmation', 'send_invite'];

    protected $dates = [
        'last_seen',
        'deleted_at',
        'created_at',
        'updated_at',
        'activated_at',
        'last_login'
    ];

    protected $userTypes = [
        0 => 'client',
        1 => 'worker'
    ];

    public static $loginAttribute = null;

    public $belongsTo = [
        'OrgLegalType'  => LegalType::class, 'id', 'org_legal_type_id',
        'Country'       => Country::class, 'id', 'country_id',
        'Region'        => Region::class, 'id', 'region_id'
    ];

    public $hasMany = [
        'userServices' => [
            'skillset\Services\Models\ServiceToUser',
            'table' => 'skillset_services_sub_to_user',
            'order' => 'id'
        ]
    ];

    public $hasOne = [
        'services_count' => [ServiceToUser::class, 'count' => true],
    ];

    public function ServiceToUser()
    {
        return $this->hasMany(ServiceToUser::class);
    }

    public function NotificationBlocks()
    {
        return $this->hasMany(UserNotificationBlock::class);
    }

//    public function OrgLegalType()
//    {
//        return $this->hasOne(LegalType::class, 'id', 'org_legal_type_id');
//    }


    public function isBanned()
    {
        return false;
    }

    public function isSuspended()
    {
        return false;
    }

    public function SubServicesToUser()
    {
        return $this->hasMany(ServiceToUser::class)->with('SubServicePlain');
    }


    public function getUserTypeID($UserType)
    {
        return Arr::get(array_flip($this->userTypes), $UserType);
    }

    public function SubServies()
    {
        return $this->hasManyThrough(SubService::class, ServiceToUser::class, 'user_id', 'id', 'id', 'services_sub_id');
    }

    /**
     * Sends the confirmation email to a user, after activating.
     * @param  string $code
     * @return bool
     */

    public function getUserType()
    {
        return Arr::get($this->userTypes, config('auth.UserType', 0));
    }


    public function signUp($request)
    {
        $authFields = ['username', 'password', 'password_confirmation', 'user_type', 'name', 'surname', 'id_number', 'country_id', 'region_id', 'address', 'legal_entity','org_legal_type_id', 'org_title', 'org_id_number'];
        $credentials = $request->only($authFields);
        if (array_key_exists('org_legal_type_id', $credentials) && (!Arr::get($credentials, 'org_legal_type_id') OR Arr::get($credentials, 'org_legal_type_id') == 'null')) {
            unset($credentials['org_legal_type_id']);
        }
        if (isset($credentials['org_title']) && $credentials['org_title'] == 'null') {
            unset($credentials['org_title']);
        }

        try {
            $userModel = User::create($credentials);
            $userModel->avatar = $request->file('avatar');
            if ($request->input('user_type') == 1) {
                $userModel->status_id = 0;
                $userModel->commission_free_till = Carbon::now()->addDays($this->getConfig('commision_free_period'))->toDateTimeString();
                if ($request->file('id_card')) {
                    $userModel->id_card = $request->file('id_card');
                }
            } else {
                $userModel->status_id = 1;
            }
            $userModel->save();
            $user = [
                'id' => $userModel->id,
                'name' => $userModel->name,
                'surname' => $userModel->surname,
                'username' => $userModel->username,
                'email' => $userModel->email,
                'is_activated' => $userModel->is_activated,
            ];

        } catch (\Exception $e) {
            traceLog($e->getMessage().' '.$e->getTraceAsString());
            return false;
        }

        $userModel = self::find($userModel->id);

        $token = JWTAuth::fromUser($userModel);

        return [
            'user'  => $user,
            'token' =>$token
        ];

    }
    //
    // Constructors
    //

    /**
     * Looks up a user by their email address.
     * @return self
     */
    public static function findByUsername($username)
    {
        return $username ? self::where('username', $username)->first() : null;
    }

    /**
     * Returns the public image file path to this user's avatar.
     */
    public function getAvatar($size = 300, $options = null)
    {
        return [
            'path'  => $this->avatar ? $this->avatar->path : '',
            'thumb' => $this->avatar ? $this->avatar->getThumb($size, $size, []) : ''
        ];
    }

    /**
     * Returns the minimum length for a new password from settings.
     * @return int
     */
    public static function getMinPasswordLength()
    {
        return config('rainlab.user::minPasswordLength', 8);
    }

    //
    // Events
    //

    /**
     * Before validation event
     * @return void
     */
    public function beforeValidate()
    {
        /*
         * Guests are special
         */
        if ($this->is_guest && !$this->password) {
            $this->generatePassword();
        }

        /*
         * When the username is not used, the email is substituted.
         */
        if (
            (!$this->username) ||
            ($this->isDirty('email') && $this->getOriginal('email') == $this->username)
        ) {
            $this->username = $this->email;
        }

        /*
         * Apply Password Length Settings
         */
//        $minPasswordLength = static::getMinPasswordLength();
//        $this->rules['password'] = "required:create|between:$minPasswordLength,255|confirmed";
//        $this->rules['password_confirmation'] = "required_with:password|between:$minPasswordLength,255";
    }

    /**
     * After create event
     * @return void
     */
    public function afterCreate()
    {
        $this->restorePurgedValues();

        if ($this->send_invite) {
            $this->sendInvitation();
        }
    }

    /**
     * Before login event
     * @return void
     */
    public function beforeLogin()
    {
        if ($this->is_guest) {
            $login = $this->getLogin();
//            throw new AuthException(sprintf(
//                'Cannot login user "%s" as they are not registered.', $login
//            ));
        }

        parent::beforeLogin();
    }

    /**
     * After login event
     * @return void
     */
    public function afterLogin()
    {
        $this->last_login = $this->freshTimestamp();

        if ($this->trashed()) {
            $this->restore();

            Mail::sendTo($this, 'rainlab.user::mail.reactivate', [
                'name' => $this->name
            ]);

            Event::fire('rainlab.user.reactivate', [$this]);
        }
        else {
            parent::afterLogin();
        }

        Event::fire('rainlab.user.login', [$this]);
    }

    /**
     * After delete event
     * @return void
     */
    public function afterDelete()
    {
        if ($this->isSoftDelete()) {
            Event::fire('rainlab.user.deactivate', [$this]);
            return;
        }

        $this->avatar && $this->avatar->delete();

        parent::afterDelete();
    }

    public function getUserNameType($Username = null)
    {
        $Username = $Username ?: $this->username;
        if (filter_var( $Username, FILTER_VALIDATE_EMAIL )) {
            return 'email';
        }
        return 'phone';
    }

    public function getUserNameValidationRules($unique = false, $required = true)
    {
        $rule = ['min:9','max:100'];
        if ($unique) {
            $rule = array_merge($rule, [new UniqPhone()]);
        }
        if ($required) {
            $rule = array_merge($rule, ['required']);
        }
        return $rule;
    }


    public function generateCode()
    {
        return rand(10000,99999);
    }

    public function sendVerificationCode($params = [])
    {
        $Data = [
            'code'    => $this->generateCode(),
            'username' => Arr::get($params,'username')
        ];

        if ($this->getUserNameType(Arr::get($params,'username')) == 'email') {
            $this->sendVerificationMail(Arr::get($params,'username'), Arr::get($Data, 'code'));
        } else {
            $this->sendVerificationSMS(Arr::get($params,'username'), Arr::get($Data, 'code'));
        }
        $payload = JWTFactory::sub('token')->data($Data)->make();
        $Data['token'] = JWTAuth::encode($payload)->get();
        unset($Data['code']);
        return $Data;
    }

    public function checkVerficiationCode($params = [])
    {
        try {
            JWTAuth::setToken(Arr::get($params,'token'));
            $token = JWTAuth::getToken();
            $tokenData = JWTAuth::getPayload($token)->toArray();
        } catch (\Exception $e) {
            throw new \Exception('invalid_token', self::$ERROR_CODES['VERIFICATION_ERROR']);
        }

        if (!$tokenData) {
            throw new \Exception('invalid_token', self::$ERROR_CODES['VERIFICATION_ERROR']);
        }
        if (Arr::get($tokenData, 'data.code') != Arr::get($params,'code') || Arr::get($tokenData, 'data.username') != Arr::get($params,'username')) {
            throw new \Exception('invalid_code', self::$ERROR_CODES['VERIFICATION_ERROR']);
        }
        return true;
    }


    /**
     * Assigns this user with a random password.
     * @return void
     */
    protected function generatePassword()
    {
        $this->password = $this->password_confirmation = Str::random(static::getMinPasswordLength());
    }

    /**
     * TODO implement sendVerificationMail body
     */
    private function sendVerificationMail($mail, $code)
    {
        Mail::send('rainlab.user::mail.activate', ['code' => $code], function($message) use ($mail, $code) {
            $message->to($mail, $mail);
            (new EmailLog)->create([
                'to_mail'       => $mail,
                'text'          => $code
            ]);
        });

    }

    /**
     * TODO implement sendVerificationSMS body
     */
    private function sendVerificationSMS($Phone, $Code)
    {
        $smsText = 'Hi, Your verification code is: '.$Code;
        $this->SendSMS($Phone, $smsText);
    }

    public function login($request)
    {
        $login_fields = ['username', 'password'];
        $credentials = $request->only($login_fields);
        try {
            // verify the credentials and create a token for the user
            if (! $token = JWTAuth::attempt($credentials)) {
                return false;
            }
        } catch (JWTException $e) {
            // something went wrong
            return false;
        }
        $userModel = JWTAuth::authenticate($token);

        $user = [
            'id' => $userModel->id,
            'name' => $userModel->name,
            'surname' => $userModel->surname,
            'username' => $userModel->username,
            'email' => $userModel->email,
            'is_activated' => $userModel->is_activated,
        ];
        // if no errors are encountered we can return a JWT
        return [
            'user'  => $user,
            'token' =>$token
        ];
    }

    public function getInfo($request, $id = null)
    {
        $User = $id ? $this->where('id', $id)->first() : JWTAuth::authenticate($request->bearerToken());
        if (!$User) {
            return false;
        }
        if ($User->status_id < 0) {
            return false;
        }

        $Data = $this->filterInfo($User, $id);
        $Data['gallery'] = [];
        foreach ($User->gallery AS $photo) {
            $Data['gallery'][] = [
                'path'    =>   $photo->getPath(),
                'thumb'   =>   $photo->getThumb(100, 100, ['mode' => 'crop'])
            ];
        }

        $Data['services'] = (new ServiceToUser)->getUserServices($User->id);
        $Data['services_count'] = count($Data['services']);
        $Data['rating'] = Arr::get((new Rating)->getAll(['user_id' => $User->id, 'limit', 5]), 'rating');
        $OrderObj = (new Order());
        $UserOrders = $OrderObj->where($OrderObj->getWhereUser(Arr::get($User, 'user_type')), $id ?: config('auth.UserID'));

        $finishedOrders = (clone $UserOrders)->where('status_id', $OrderObj->statuses['user_payed'])->count();
        $activeOrders = (clone $UserOrders)->whereIn('status_id', [$OrderObj->statuses['work_started'], $OrderObj->statuses['work_finished_by_worker'], $OrderObj->statuses['user_accepted']])->count();

        $JobOrders = JobOrder::where(function ($query) use ($id) {
            return $query->whereHas('offer', function ($q) use ($id) {
                return $q->where('author_id', $id ?: config('auth.UserID'));
            })->orWhereHas('offer.job', function ($q) use ($id) {
                return $q->where('user_id', $id ?: config('auth.UserID'));
            });
        });

        $jobOrder = new JobOrder();

        $finishedJobOrdersCount = (clone $JobOrders)->where('status', $jobOrder->statuses['paid'])->count();
        $activeJobOrdersCount = (clone $JobOrders)->whereIn(
            'status',
            [
                $jobOrder->statuses['pending_payment'],
                $jobOrder->statuses['work_started'],
                $jobOrder->statuses['work_finished_by_worker']
            ],
        )->count();

        $Data['order_status_counts'] = [
            'finished'  => $finishedOrders + $finishedJobOrdersCount,
            'active'    => $activeOrders + $activeJobOrdersCount
        ];

        return $Data;
    }

    public function filterInfo ($User, $public = true, $details = false, $customUserAddress = null) {
        if (!$User) {
            return [];
        }
        $Fields = $public ? $this->publicInfo : $this->privateInfo;
        $Return = [];

        foreach ($Fields AS $field) {
            $Return[$field] = Arr::get($User, $field);
        }
        if ($details) {
            $Return['address'] = $this->getUserFullAddress($User, $customUserAddress);
        }
        $Return['avatar'] = $User->getAvatar();
        return $Return;
    }

    public function edit($request)
    {
        $UserID = config('auth.UserID');
        if ($UserID === null) {
            throw new \Exception('invalid_token', self::$ERROR_CODES['AUTH_ERROR']);
        }
        $userModel = JWTAuth::authenticate($request->bearerToken());
        if ($request->input('password')) {
            $this->changePassword($request);
        }

        $editFields = ['email', 'name', 'surname', 'id_number', 'phone_number', 'country_id', 'region_id', 'address', 'description', 'org_legal_type_id', 'org_id_number', 'org_title', 'username'];
        $credentials = $request->only($editFields);
        $updatedFields = $this->logUpdatedFields($credentials, $userModel);
        if (!empty(json_decode($updatedFields, 1)) && $userModel->status_id > 0) {
            $credentials['updated_fields'] = $updatedFields;
            $credentials['status_id'] = 2;
        }
        $credentials['region_id'] = (int)Arr::get($credentials, 'region_id', 0) ?? 0;
        $credentials['country_id'] = (int)Arr::get($credentials, 'country_id', 0) ?? 0;

        $userModel->update($credentials);
        if ($request->file('avatar')) {
            $userModel->avatar = $request->file('avatar');
            if ($userModel->status_id > 0) {
                $userModel->updated_fields = $this->logUpdatedFields(['avatar' => ''], $userModel);
                $userModel->status_id = 2;
            }
            $userModel->save();
        }
        if (($request->input('avatar', null) === "") AND $userModel->avatar) {
            if ($userModel->status_id > 0) {
                $userModel->updated_fields = $this->logUpdatedFields(['avatar' => ''], $userModel);
                $userModel->status_id = 2;
            }
            $userModel->avatar->delete();
            $userModel->avatar = null;
        }
        $Return = $userModel->toArray();
        $Return['avatar'] = $userModel->getAvatar();
        return $Return;
    }

    public function changePassword($request)
    {
        $userModel = JWTAuth::authenticate($request->bearerToken());

        if (!$userModel->checkPassword($request->input('old_password'))) {
            throw new Exception('incorrect_old_password', self::$ERROR_CODES['VALIDATION_ERROR']);
        }

        $userModel->password = $request->input('password');
        $userModel->rules = [];
        $userModel->save();
        return true;
    }

    public function updateGallery($request)
    {
        $PhotosCount = (is_array($request->file('gallery')) ? count($request->file('gallery')) : 0) + (is_array($request->input('existing_gallery')) ? count($request->input('existing_gallery')) : 0);
        if ($PhotosCount > $this->getConfig('gallery_max_photos', 5)) {
            throw new \Exception('gallery limit reached', self::$ERROR_CODES['FORBIDDEN']);
        }
        $userModel = JWTAuth::authenticate($request->bearerToken());
        foreach ($userModel->gallery AS $photo) {
            if (!$request->input('existing_gallery') OR !is_array($request->input('existing_gallery')) OR !in_array($photo->path, $request->input('existing_gallery'))) {
                $photo->delete();
            }
        }

        $userModel->gallery = $request->file('gallery');
        $userModel->save();

        return $this->getGallery($request);
    }

    public function getGallery($request)
    {
        $userModel = JWTAuth::authenticate($request->bearerToken());
        $Return = [];
        foreach ($userModel->gallery AS $photo) {
            $Return[] = [
                'path'    =>   $photo->getPath(),
                'thumb'   =>   $photo->getThumb(100, 100, ['mode' => 'crop'])
            ];
        }
        return $Return;
    }

    public function changeStatus($request)
    {
        $userModel = JWTAuth::authenticate($request->bearerToken());
        if ($userModel->forced_busy) {
            throw new \Exception('You have unfinished orders and can not change status', self::$ERROR_CODES['FORBIDDEN']);
        }
        $userModel->is_busy = $request->input('is_busy');
        $userModel->save();
        return ['is_busy' => $userModel->is_busy];
    }

    public function sendResetPasswordCode($params)
    {
        $code       = $this->generateCode();
        $username   = Arr::get($params,'username');

        $User = self::findByUsername($username);

        if (!$User) {
            throw new Exception($this->getUserNameType($username) ? 'wrong email' : 'wrong phone number', self::$ERROR_CODES['NOT_FOUND']);
        }

        $User->reset_password_code = $code;
        $User->save();
        if ($this->getUserNameType($username) == 'email') {
            Mail::send('rainlab.user::mail.restore', ['code' => $code], function($message) use ($username) {
                $message->to($username, $username);
            });
            (new EmailLog)->create([
                'to_mail'       => $username,
                'text'          => $code
            ]);
        } else {
            $this->SendSMS($username, 'Hi, your password reset is: '.$code);
        }
        return [];
    }

    public function resetPassword($params = [])
    {
        $username   = Arr::get($params,'username');
        $User = self::findByUsername($username);
        if (!$User || !($User->reset_password_code) || $User->reset_password_code != Arr::get($params, 'code')) {
            throw new \Exception('wrong code', self::$ERROR_CODES['PASSWORD_RESET_ERROR']);
        }
        $User->password = Arr::get($params, 'password');
        $User->reset_password_code = null;
        $User->rules = [];
        $User->save();
        return true;
    }

    public function updateUserLang($request)
    {
        $User = JWTAuth::authenticate($request->bearerToken());
        $User->lang = $this->checkLang($request->input('lang'));
        $User->save();
        return [
            'user_lang' => $User->lang
        ];
    }

    public function checkUserBusyStatus($UserID)
    {
        $OrderModel = (new Order);
//        $OfferWorkerModel = (new OfferWorker);
//        $OffersCount = $OfferWorkerModel->where('worker_id', $UserID)->whereIn('status_id', [$OfferWorkerModel->statuses['chat_created'], $OfferWorkerModel->statuses['offer_accepted_by_worker']])->count();
        $OrdersCount = $OrderModel->where('worker_id', $UserID)->whereIn('status_id', [$OrderModel->statuses['work_started'], $OrderModel->statuses['work_finished_by_worker'], $OrderModel->statuses['user_accepted']])->count();
        $IsBusy = $OrdersCount >= $this->getConfig('is_busy_on_active_orders_count');
        self::find($UserID)->update([
            'is_busy'       => (int) $IsBusy,
            'forced_busy'   => (int) $IsBusy
        ]);
    }

    public function getUserFullAddress($User, $customUserAddress)
    {
        if (Arr::get($User, 'id') == 0) {
            return $customUserAddress;
        }
        $Country = Arr::get($User, 'Country.title');
        $Region = Arr::get($User, 'Region.title');
        $Address = Arr::get($User, 'address');
        return ($Country ? $Country.', ' : '').($Region ? $Region.', ' : '').$Address;
    }

    public function logUpdatedFields($credentials, $User)
    {
        $User = $User->toArray();
        $UpdatedFields = json_decode(Arr::get($User,'updated_fields'), 1);
        $Return = is_array($UpdatedFields) ? $UpdatedFields : [];
        if (arr::get($credentials, 'avatar') !== null) {
            $Return['avatar'] = '';
            unset($credentials['avatar']);
        }
        if (arr::get($credentials, 'services') !== null) {
            $Return['services'] = '';
            unset($credentials['services']);
        }
        if (Arr::get($credentials,'region_id') && Arr::get($credentials,'region_id') != Arr::get($User,'region_id')) {
            if ($region = (new Region)->find(Arr::get($User,'region_id'))) {
                $Return['Region'] = Arr::get($region,'title');
                unset($credentials['region_id']);
            }
        }
        if (Arr::get($credentials,'country_id') && Arr::get($credentials,'country_id') != Arr::get($User,'country_id')) {
            if ($country = (new Country)->find(Arr::get($User,'country_id'))) {
                $Return['Country'] = $country->title;
                unset($credentials['country_id']);
            }
        }

        if (Arr::get($credentials, 'org_legal_type_id') && Arr::get($credentials, 'org_legal_type_id') != Arr::get($User,'org_legal_type_id')) {
            if ($LegalType = (new LegalType)->find(Arr::get($User,'org_legal_type_id'))) {
                $Return['OrgLegalType'] = $LegalType->title;
                unset($credentials['org_legal_type_id']);
            }
        }

        foreach ($credentials AS $key => $credential) {
            if ($credential != Arr::get($User,$key)) {
                $Return[$key] = Arr::get($User, $key);
            }
        }
        return json_encode($Return);
    }

    public function deleteMyUser(\Illuminate\Http\Request $request)
    {
        $User = JWTAuth::authenticate($request->bearerToken());
        if (!$User) {
            throw new \Exception('invalid_token', self::$ERROR_CODES['AUTH_ERROR']);
        }
        if (!$User->checkPassword($request->input('password'))) {
            throw new Exception('incorrect_password', self::$ERROR_CODES['VALIDATION_ERROR']);
        }
        $User->status_id = -1;
        $User->username = $User->username.' (Deleted)';
        $User->surname = $User->surname.' (Deleted)';
        $User->save();
    }

    public function fillBalance($params = [])
    {
        return (new Payment)->fillBalance(config('auth.UserID'), Arr::get($params, 'amount'));
    }

    public function updateBalance($userId, $price, $add = true)
    {
        $User = self::find($userId);
        $User->balance = $add ? ($User->balance + $price) : ($User->balance - $price);
        $User->save();
        if ($add AND $User->balance >= 0) {
            $User->update(['is_unactive' => 0]);
        }
    }

    public function getCommission($userId)
    {
        $user = self::find($userId);

        if ($user->app_commission_percent) {
            return $user->app_commission_percent;
        }
        if ($user->commission_free_till && Carbon::createFromFormat('Y-m-d H:i:s', $user->commission_free_till)->isFuture()) {
            return 0;
        }
        return $this->getRateCommission($user->rate);
    }

    public function scopeActive($query)
    {
        return $query->where('status_id', 1)->where('is_unactive', 0);
    }

    public function getAll($params = [])
    {
        $query = self::active()
            ->when(Arr::get($params, 'keyword'), function ($query, $keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('surname', 'like', '%' . $keyword . '%');
                });
            });

        return $query->get()->map(function ($user) {
            return $this->filterInfo($user);
        });
    }
}
