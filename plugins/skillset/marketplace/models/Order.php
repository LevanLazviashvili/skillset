<?php namespace skillset\Marketplace\Models;

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
    public $table = 'skillset_marketplace_orders';

    /**
     * @var array The attributes that are mass assignable.
     */
    protected $fillable = [
        'offer_id',
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
        'completed_at'
    ];

    protected $purgeable = ['seller_user', 'client_user'];

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

    public $hasMany = [
        'orderProducts' => [Product::class, 'order_id', 'id', 'conditions' => 'pre = 0'],
        'offerProducts' => [Product::class, 'order_id', 'id', 'conditions' => 'pre = 1'],
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
        'pending'                      => 1,
        'pending_payment'              => 2,
        'client_paid'                  => 3,
        'contract_ready'               => 4,
        'paid'                         => 5
    ];

    public function products()
    {
        return $this->hasMany(Product::class)->where('pre', false);
    }

    public $hasOne = [
        'application' => [Application::class, 'through' => Offer::class, 'application_id', 'offer_id'],
    ];

    public function rates()
    {
        return $this->hasMany(Rating::class, 'order_id', 'id')->where('order_type', (new Rating())->orderTypes['marketplaceOrder']);

    }

    public function application()
    {
        return $this->hasOne(Application::class, 'id', 'offer_id')
            ->join('skillset_marketplace_offers', 'skillset_marketplace_offers.application_id', '=', 'skillset_marketplace_applications.id')
            ->select('skillset_marketplace_applications.*');
    }

    public function getUserByRole($orderInstance, $role)
    {
        $order = clone $orderInstance;

        $order->loadMissing(['offer.application']);

        $app = $order->offer->application;
        $isAuthorSeller = $app->trade_type == $app->tradeTypes['sell'];

        if ($role === 'seller') {
            return User::find($isAuthorSeller ? $app->user_id : $order->offer->user_id);
        }else if ($role === 'client') {
            return User::find(!$isAuthorSeller ? $app->user_id : $order->offer->user_id);
        }

        return 0;
    }

    public function getClientUserAttribute()
    {
        $client = $this->getUserByRole($this, 'client');

        return "($client->id) $client->name $client->surname";
    }

    public function getSellerUserAttribute()
    {
        $seller = $this->getUserByRole($this, 'seller');

        return "($seller->id) $seller->name $seller->surname";
    }
}
