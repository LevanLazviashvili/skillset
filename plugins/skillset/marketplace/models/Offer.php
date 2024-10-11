<?php namespace skillset\Marketplace\Models;

use Model;
use RainLab\User\Models\User;
use skillset\Conversations\Models\Message;

/**
 * Model
 */
class Offer extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'skillset_marketplace_offers';

    /**
     * @var array The attributes that are mass assignable.
     */
    protected $fillable = [
        'application_id',
        'user_id',
        'conversation_id',
        'payment_type',
        'status',
    ];

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $belongsTo = [
        'user'       => [User::class, 'key' => 'user_id', 'otherKey' => 'id'],
        'application'       => [Application::class, 'key' => 'application_id', 'otherKey' => 'id'],
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

    public function order()
    {
        return $this->hasOne(Order::class, 'offer_id', 'id');
    }

    public $paymentTypes = [
        'cash'      => 0,
        'balance'   => 1
    ];

    public $statuses = [
        'offer_created'                 => 1,
        'offer_invoice_sent'            => 2,
        'offer_accepted'                => 3,
        'offer_rejected'                => 4,
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
