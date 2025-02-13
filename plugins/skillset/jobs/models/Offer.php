<?php namespace skillset\Jobs\Models;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Model;
use RainLab\User\Models\User;
use skillset\Conversations\Models\Message;
use skillset\Notifications\Models\Notification;

/**
 * Model
 */
class Offer extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'skillset_jobs_offers';

    /**
     * @var array The attributes that are mass assignable.
     */
    protected $fillable = [
        'job_id',
        'author_id',
        'conversation_id',
        'estimated_completed_date',
        'payment_type',
        'status',
    ];

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $belongsTo = [
        'author'       => [User::class, 'key' => 'author_id', 'otherKey' => 'id'],
        'job'       => [Job::class, 'key' => 'job_id', 'otherKey' => 'id'],
    ];

    public $hasMany = [
        'unread' => [
            Message::class,
            'key' => 'conversation_id',
            'otherKey' => 'conversation_id',
            'conditions' => 'seen = 0 AND user_id NOT IN (SELECT id FROM users WHERE admin_user_id IS NOT NULL)',
        ],
        'unread_count' => [
            Message::class,
            'key' => 'conversation_id',
            'otherKey' => 'conversation_id',
            'conditions' => 'seen = 0 AND user_id NOT IN (SELECT id FROM users WHERE admin_user_id IS NOT NULL)',
            'count' => true
        ],
    ];

    public function Order()
    {
        return $this->hasOne(Order::class, 'offer_id', 'id');
    }

    public $paymentTypes = [
        'cash'      => 0,
        'balance'   => 1
    ];

    public $statuses = [
        'offer_created'                 => 1,
        'offer_accepted_by_worker'      => 2,
        'offer_rejected_by_worker'      => 3,
        'offer_rejected_by_client'      => 4,
        'offer_accepted_by_client'      => 5
    ];

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function getUserByRole($offerInstance, $role)
    {
        $offer = clone $offerInstance;

        $offer->loadMissing(['job']);

        $job = $offer->job;
        $isClientRole = $job->author_role == $job->authorRoles['client'];
        $isWorkerRole = $job->author_role == $job->authorRoles['worker'];

        if ($role === 'client') {
            return User::find($isClientRole ? $job->user_id : $offer->author_id);
        } elseif ($role === 'worker') {
            return User::find($isWorkerRole ? $job->user_id : $offer->author_id);
        }

        return 0;
    }
}
