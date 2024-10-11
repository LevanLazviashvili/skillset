<?php namespace skillset\Marketplace\Models;

use Model;
use skillset\details\Models\Unit;

/**
 * Model
 */
class Product extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'skillset_marketplace_application_product';

    /**
     * @var array The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'amount',
        'unit_id',
        'unit_price',
        'offer_id',
        'order_id',
        'pre',
        'status',
    ];

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $belongsTo = [
        'offer'       => [Offer::class, 'key' => 'offer_id', 'otherKey' => 'id'],
        'order'       => [Order::class, 'key' => 'order_id', 'otherKey' => 'id'],
        'unit'       => [Unit::class, 'key' => 'unit_id', 'otherKey' => 'id'],
    ];

    public $hasOneThrough = [
        'application' => [Application::class, 'through' => Offer::class],
    ];
}
