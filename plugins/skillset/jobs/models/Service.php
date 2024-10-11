<?php namespace skillset\Jobs\Models;

use Model;
use RainLab\User\Models\User;
use skillset\details\Models\Unit;

/**
 * Model
 */
class Service extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'skillset_jobs_services';

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
        'job' => [Job::class, 'through' => Offer::class],
    ];
}
