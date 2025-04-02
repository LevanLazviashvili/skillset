<?php namespace skillset\Jobs\Models;

use Illuminate\Support\Arr;
use Model;
use RainLab\User\Models\User;
use skillset\Rating\Models\Rating;

/**
 * Model
 */
class Order extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'skillset_jobs_orders';

    /**
     * @var array The attributes that are mass assignable.
     */
    protected $fillable = [
        'offer_id',
        'estimated_completed_date',
        'completed_at',
        'price',
        'total_price',
        'app_percent',
        'app_percent_amount',
        'bank_percent',
        'bank_percent_amount',
        'payment_type',
        'payment_hash',
        'payment_order_id',
        'status',
        'rated',
        'end_date_notified'
    ];

    protected $hidden = [
        'payment_hash'
    ];

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $belongsTo = [
        'offer' => [Offer::class, 'key' => 'offer_id', 'otherKey' => 'id'],

    ];

    public $hasOne = [
        'job' => [Job::class, 'through' => Offer::class, 'job_id', 'offer_id'],
    ];

    public $hasMany = [
        'orderServices' => [Service::class, 'order_id', 'id', 'conditions' => 'pre = 0'],
        'offerServices' => [Service::class, 'order_id', 'id', 'conditions' => 'pre = 1'],
        'orderRate' => [
            'skillset\rating\Models\Rating',
            'table' => 'skillset_rating_',
            'order' => 'id',
            'key' => 'order_id',
            'conditions' => 'order_type = 2',
            'limit' => 1
        ]
    ];

    public $paymentTypes = [
        'cash'      => 0,
        'balance'   => 1
    ];

    public $statuses = [
        'pending_payment'         => 1,
        'work_started'            => 2,
        'work_finished_by_worker' => 3,
        'paid'                    => 4
    ];

    public function services()
    {
        return $this->hasMany(Service::class)->where('pre', false);
    }

    public function rates()
    {
        return $this->hasMany(Rating::class, 'order_id', 'id')->where('order_type', (new Rating())->orderTypes['jobOrder']);

    }

    public function getData($params = [])
    {
        $authUserId = config('auth.UserID');

        $query = self::with(['offer.job.user', 'offer.author'])->where(function ($query) use ($authUserId) {
            return $query->whereHas('offer', function ($q) use ($authUserId) {
                return $q->where('author_id', $authUserId);
            })->orWhereHas('offer.job', function ($q) use ($authUserId) {
                return $q->where('user_id', $authUserId);
            });
        });

        if ($keyword = Arr::get($params, 'keyword')) {
            $query->whereHas('offer.job', function ($q) use ($keyword) {
                return $q->where('title', 'like', '%' . $keyword . '%')
                    ->orWhere('description', 'like', '%' . $keyword . '%')
                ;
            });
        }

        $query->orderBy('id', 'desc');

        $orders = $query->paginate($params['per_page'] ?? 10);

        $user = new User();

        $orders->setCollection($orders->getCollection()->map(function ($item) use ($user) {
            $clientInfo = $user->filterInfo($this->getUserByRole($item, 'client'));
            $workerInfo = $user->filterInfo($this->getUserByRole($item, 'worker'));
            $job = $item->offer->job;

            unset($job->user);

            $item['client'] = $clientInfo;
            $item['worker'] = $workerInfo;
            $item['job'] = $job;

            $item = $item->toArray();

            unset($item['offer']);
            return $item;
        }));

        return $orders;
    }

    public function getUserByRole($orderInstance, $role)
    {
        $order = clone $orderInstance;

        $order->loadMissing(['offer.job']);

        $job = $order->offer->job;
        $isClientRole = $job->author_role == $job->authorRoles['client'];
        $isWorkerRole = $job->author_role == $job->authorRoles['worker'];

        if ($role === 'client') {
            return User::find($isClientRole ? $job->user_id : $order->offer->author_id);
        } elseif ($role === 'worker') {
            return User::find($isWorkerRole ? $job->user_id : $order->offer->author_id);
        }

        return 0;
    }

    public function getClientUserAttribute()
    {
        $client = $this->getUserByRole($this, 'client');

        return "($client->id) $client->name $client->surname";
    }

    public function getWorkerUserAttribute()
    {
        $worker = $this->getUserByRole($this, 'worker');

        return "($worker->id) $worker->name $worker->surname";
    }

    public function job()
    {
        return $this->hasOne(Job::class, 'id', 'offer_id')
            ->join('skillset_jobs_offers', 'skillset_jobs_offers.job_id', '=', 'skillset_jobs.id')
            ->select('skillset_jobs.*');
    }

}
