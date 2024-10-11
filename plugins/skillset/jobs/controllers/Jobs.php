<?php namespace skillset\Jobs\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Carbon\Carbon;
use Cms\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Namshi\JOSE\Signer\OpenSSL\None;
use RainLab\User\Models\User;
use skillset\Configuration\Traits\Config;
use skillset\Conversations\Models\Conversation;
use skillset\Conversations\Models\Message;
use skillset\Jobs\Models\Job;
use skillset\Jobs\Models\Offer;
use skillset\Notifications\Models\Notification;
use skillset\orders\models\Advert;
use skillset\Payments\Models\Payment;

class Jobs extends Controller
{
    use ApiResponser;
    use Config;

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
        BackendMenu::setContext('skillset.Jobs', 'main-menu-item');
    }

    public function get(Request $request)
    {
        $rules = [
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1',
            'user_id' => 'integer|min:1',
            'region_id' => 'nullable|string',
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

        $query = Job::with(['user'])->publicVisible();

        $this->applyFilters($query, $params);

        $jobs = $query->paginate($params['per_page'] ?? 10);

        $user = new User();

        $jobs->setCollection($jobs->getCollection()->map(function ($item) use ($user) {
            $userInfo = $user->filterInfo($item->user);

            $item = $item->toArray();

            $item['user'] = $userInfo;

            return $item;
        }));

        $user = User::find(config('auth.UserID'));
        $user->update(['last_seen_jobs' => now()]);

        return $this->response([
            'jobs' => $jobs,
        ]);
    }

    public function userJobs(Request $request)
    {
        $rules = [
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1',
            'keyword' => 'sometimes|string',
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

        $query = Job::with(['user'])
            ->where('user_id', config('auth.UserID'))
            ->when(Arr::get($params, 'status'), function ($query, $status) {
                return $query->where('status', $status);
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
            ->where('status', (new Job())->statuses['new']);

        $query->orderBy('type')
            ->orderBy('id', 'desc')
            ->orderBy('active', 'desc');

        $jobs = $query->paginate($params['per_page'] ?? 10);

        $user = new User();

        $jobs->setCollection($jobs->getCollection()->map(function ($item) use ($user) {
            $userInfo = $user->filterInfo($item->user);

            $item = $item->toArray();

            $item['user'] = $userInfo;

            return $item;
        }));

        return $this->response([
            'jobs' => $jobs,
        ]);
    }

    public function show($lang, $id)
    {
        $job = Job::where(function ($query) {
            $query->active()->orWhere('user_id', config('auth.UserID'));
        })
            ->where('id', $id)
            ->first();

        if(!$job){
            return $this->errorResponse('Not Found', self::$ERROR_CODES['NOT_FOUND']);
        }

        $job->load(['user', 'region', 'video', 'images']);

        $user = (new User)->filterInfo($job->user);

        $job = $job->toArray();

        $job['user'] = $user;

        return $this->response([
            'job' => $job,
        ]);
    }

    public function store(Request $request)
    {
        $rules =  [
            'author_role' => 'required|in:1,2',
            'title' => 'required|string',
            'description' => 'required|string',
            'region_id' => 'nullable|integer|exists:skillset_details_regions,id',
            'price' => 'nullable|numeric',
            'type' => 'required|integer|in:1,2',
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

        $job = new Job($validatedData);

        $job->video = $request->file('video') ?? '';

        $job->images = $validatedData['images'] ?? [];

        $job->save();

        (new Notification())->notifyUsersAboutNewJobAdded($job->id);

        if ($job->type == $job->types['vip']){
            $job->update(['type' => $job->types['free']]);
            return $this->successResponse((new Payment)->buyVip($job, 'job'));
        }

        return $this->successResponse([]);
    }

    public function contact($lang, $id)
    {
        $job = Job::find($id);

        if(!$job){
            return $this->errorResponse('Not Found', self::$ERROR_CODES['NOT_FOUND']);
        }

        $authUserId = config('auth.UserID');

        $offer = Offer::where('author_id', $authUserId)->where('job_id', $job->id)->first();

        if(!$offer){
            $conversationId = (new Conversation())->startNewConversation([$authUserId, $job->user_id], $authUserId);

            $offer = Offer::create([
                'job_id' => $job->id,
                'author_id' => $authUserId,
                'conversation_id' => $conversationId
            ]);

            (new Message)->sendSystemMessage($conversationId, 'chat_created');
        }

        return $this->successResponse([
            'offer_id' => $offer->id,
            'conversation_id' => $offer->conversation_id
        ]);
    }

    public function renew($lang, $id)
    {
        $authUserId = config('auth.UserID');

        $job = Job::where('id', $id)
            ->where('user_id', $authUserId)
            ->first();

        if(!$job){
            return $this->errorResponse('Not Found', self::$ERROR_CODES['NOT_FOUND']);
        }

        $now = now();

        if (!$job->active ||
            $job->updated_at->toDateTimeString() > $now->copy()->subDay()->toDateTimeString() ||
            $job->updated_at->toDateTimeString() < $now->copy()->subDays(2)->toDateTimeString()
        ){
            return $this->errorResponse('Forbidden', 403);
        }

        $job->update([
            'updated_at' => now()->toDateTimeString()
        ]);

        return $this->successResponse([]);
    }

    public function makeJobsInactive()
    {
        Job::publicVisible()
            ->where('updated_at', '<', now()->subDays(2)->toDateTimeString())
            ->update(['active' => false]);
    }

    public function updateExpiredVipJobs()
    {
        $vipPeriod = $this->getConfig('job_vip_period');

        $adverts = Advert::whereHas('payment', function ($query) use ($vipPeriod) {
            $query->where('status', 'success')->where('updated_at', '<', now()->subDays($vipPeriod)->toDateTimeString());
        })
            ->where('advertable_type', Job::class)
            ->distinct('advertable_id')
            ->with('payment')
            ->pluck('id')
            ->toArray();

        Job::publicVisible()->whereIn('id', $adverts)->update(['type' => (new Job())->types['free']]);
    }

    protected function applyFilters($query, $params)
    {
        $filters = [
            'user_id' => 'user_id',
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

        $job = Job::where('id', $id)
            ->where('user_id', $authUserId)
            ->first();

        if(!$job){
            return $this->errorResponse('Not Found', self::$ERROR_CODES['NOT_FOUND']);
        }

        return $this->successResponse((new Payment)->buyVip($job, 'job'));
    }
}
