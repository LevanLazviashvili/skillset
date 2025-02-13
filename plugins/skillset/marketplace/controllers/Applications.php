<?php namespace skillset\Marketplace\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Cms\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use RainLab\User\Models\User;
use skillset\Conversations\Models\Conversation;
use skillset\Conversations\Models\Message;
use skillset\Marketplace\Models\Application;
use skillset\Marketplace\Models\Offer;
use skillset\Notifications\Models\Notification;
use skillset\orders\models\Advert;
use skillset\Payments\Models\Payment;

class Applications extends Controller
{
    use ApiResponser;

    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController',
//        'Backend\Behaviors\RelationController'
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
//    public $relationConfig = 'config_relation.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('skillset.Marketplace', 'main-menu-item');
    }

    public function get(Request $request)
    {
        $rules = [
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1',
            'user_id' => 'integer|min:1',
            'region_id' => 'nullable|string',
            'category_id' => 'integer|in:1,2,3',
            'trade_type' => 'integer|in:1,2',
            'keyword' => 'sometimes|string',
            'min_price' => 'sometimes|numeric|min:0',
            'max_price' => 'sometimes|numeric|min:0',
            'sort' => 'sometimes|array',
            'sort.parameter' => 'required_with:sort|string|in:id,price',
            'sort.direction' => 'required_with:sort|string|in:asc,desc',
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

        $query = Application::with(['user'])->publicVisible();

        $this->applyFilters($query, $params);

        $applications = $query->paginate($params['per_page'] ?? 10);

        $user = new User();

        $applications->setCollection($applications->getCollection()->map(function ($item) use ($user) {
            $userInfo = $user->filterInfo($item->user);

            $item = $item->toArray();

            $item['user'] = $userInfo;

            return $item;
        }));

        $user = User::find(config('auth.UserID'));
        $user->update(['last_seen_marketplace' => now()]);

        return $this->response([
            'applications' => $applications,
        ]);
    }

    public function userApplications(Request $request)
    {
        $rules = [
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1',
            'keyword' => 'sometimes|string',
            'category_id' => 'sometimes|integer|in:1,2,3',
            'trade_type' => 'sometimes|integer|in:1,2',
            'status' => 'sometimes|integer|in:1,2,3',
            'active' => 'sometimes|nullable|boolean',
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

        $query = Application::with(['user'])
            ->where('user_id', config('auth.UserID'))
            ->when(Arr::get($params, 'category_id'), function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when(Arr::get($params, 'trade_type'), function ($query, $tradeType) {
                $query->where('trade_type', $tradeType);
            })
            ->when(Arr::get($params, 'status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->when(Arr::get($params, 'keyword'), function ($query, $keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->where('title', 'like', '%' . $keyword . '%')
                        ->orWhere('description', 'like', '%' . $keyword . '%');
                });
            })
            ->when(in_array(Arr::get($params, 'active'), [0, 1]), function ($query) use ($params) {
                return $query->where('active', Arr::get($params, 'active'));
            })
            ->where('status', (new Application())->statuses['new']);

        $query->orderBy('type', 'desc')
            ->orderBy('id', 'desc')
            ->orderBy('active', 'desc');

        $applications = $query->paginate($params['per_page'] ?? 10);

        $user = new User();

        $applications->setCollection($applications->getCollection()->map(function ($item) use ($user) {
            $userInfo = $user->filterInfo($item->user);

            $item = $item->toArray();

            $item['user'] = $userInfo;

            return $item;
        }));

        return $this->response([
            'applications' => $applications,
        ]);
    }

    public function show($lang, $id)
    {
        $application = Application::active()->find($id);

        if(!$application){
            return $this->errorResponse('Not Found', self::$ERROR_CODES['NOT_FOUND']);
        }

        $application->load(['user', 'region', 'video', 'images']);

        $user = (new User)->filterInfo($application->user);

        $application = $application->toArray();

        $application['user'] = $user;

        return $this->response([
            'application' => $application,
        ]);
    }

    public function store(Request $request)
    {
        $rules =  [
            'title' => 'required|string',
            'description' => 'required|string',
            'region_id' => 'nullable|integer|exists:skillset_details_regions,id',
            'country' => 'nullable|string',
            'price' => 'required|numeric',
            'quantity' => 'required|integer|min:1',
            'type' => 'required|integer|in:1,2',
            'trade_type' => 'required|integer|in:1,2',
            'category_id' => 'required|integer|in:1,2,3',
            'images' => 'nullable|array|min:1',
            'images.*' => 'required|image|mimes:jpg,png,jpeg|max:10000',
            'video' => 'nullable|mimetypes:video/mp4,video/mpeg,video/quicktime',
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

        $validatedData = $request->validate($rules);

        $validatedData['user_id'] = config('auth.UserID');

        $application = new Application($validatedData);

        $application->video = $request->file('video') ?? '';

        $application->images = $validatedData['images'] ?? [];

        $application->save();

        (new Notification())->notifyUsersAboutNewMarketplaceAppAdded($application->id);

        if ($application->type == $application->types['vip']){
            $application->update(['type' => $application->types['free']]);
            return $this->successResponse((new Payment)->buyVip($application, 'marketplace'));
        }

        return $this->successResponse([]);
    }

    public function contact($lang, $id)
    {
        $application = Application::find($id);

        if(!$application){
            return $this->errorResponse('Not Found', self::$ERROR_CODES['NOT_FOUND']);
        }

        $authUserId = config('auth.UserID');

        $offer = Offer::where('user_id', $authUserId)->where('application_id', $application->id)->first();

        if(!$offer){
            $conversationId = (new Conversation())->startNewConversation([$authUserId, $application->user_id], $authUserId);

            $offer = Offer::create([
                'application_id' => $application->id,
                'user_id' => $authUserId,
                'conversation_id' => $conversationId
            ]);

            $ClientID = $application->trade_type == (new Application)->tradeTypes['buy'] ? $application->user_id : $authUserId;
            $Client = User::find($ClientID);

            (new Message)->sendSystemMessage($conversationId, 'chat_created',[], [], $Client->lang);
        }

        return $this->successResponse([
            'offer_id' => $offer->id,
            'conversation_id' => $offer->conversation_id
        ]);
    }

    public function makeAppsInactive()
    {
        Application::publicVisible()
            ->where('updated_at', '<', now()->subDays(30)->toDateTimeString())
            ->update(['active' => false]);
    }

    public function updateExpiredVipApps()
    {
        $vipPeriod = $this->getConfig('marketplace_vip_period');

        $adverts = Advert::whereHas('payment', function ($query) use ($vipPeriod) {
            $query->where('status', 'success')->where('updated_at', '<', now()->subDays($vipPeriod)->toDateTimeString());
        })
            ->where('advertable_type', Application::class)
            ->distinct('advertable_id')
            ->with('payment')
            ->pluck('id')
            ->toArray();

        Application::publicVisible()->whereIn('id', $adverts)->update(['type' => (new Application())->types['free']]);
    }

    private function applyFilters($query, $params)
    {
        $filters = [
            'user_id' => 'user_id',
            'category_id' => 'category_id',
            'trade_type' => 'trade_type',
            'min_price' => ['price', '>='],
            'max_price' => ['price', '<=']
        ];

        foreach ($filters as $param => $field) {
            if ($value = Arr::get($params, $param)) {
                if (is_array($field)) {
                    $query->where($field[0], $field[1], $value);
                } else {
                    $query->where($field, $value);
                }
            }
        }

        $query->when(Arr::get($params, 'keyword'), function ($query, $keyword) {
            $query->where('title', 'like', '%' . $keyword . '%')
                ->orWhere('description', 'like', '%' . $keyword . '%')
                ->orWhereHas('user', function ($query) use ($keyword) {
                    $query->where(function ($query) use ($keyword) {
                        $query->where('name', 'like', '%' . $keyword . '%')
                            ->orWhere('surname', 'like', '%' . $keyword . '%');
                    });
                });
        });

        $query->when(Arr::get($params, 'region_id'), function ($query, $regionId) {
            if ($regionId !== 'all') {
                $query->where(function ($query) use ($regionId) {
                    $query->where('region_id', (int)$regionId)
                        ->orWhere('region_id', null);
                });
            }
        }, function ($query) {
            $query->where(function ($query) {
                $query->where('region_id', null)
                    ->orWhere('region_id', User::find(config('auth.UserID'))->region_id);
            });
        });

        if ($sort = Arr::get($params, 'sort')) {
            $query->orderBy($sort['parameter'], $sort['direction']);
        } else {
            $query->orderBy('type', 'desc')->orderBy('id', 'desc');
        }
    }

    public function buyVip($lang, $id)
    {
        $authUserId = config('auth.UserID');

        $application = Application::where('id', $id)
            ->where('user_id', $authUserId)
            ->first();

        if(!$application){
            return $this->errorResponse('Not Found', self::$ERROR_CODES['NOT_FOUND']);
        }

        return $this->successResponse((new Payment)->buyVip($application, 'marketplace'));
    }
}
