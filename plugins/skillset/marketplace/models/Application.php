<?php namespace skillset\Marketplace\Models;

use Illuminate\Support\Arr;
use Model;
use RainLab\User\Models\User;
use skillset\details\Models\Region;
use skillset\orders\models\Advert;

/**
 * Model
 */
class Application extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'skillset_marketplace_applications';

    /**
     * @var array The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'description',
        'quantity',
        'price',
        'user_id',
        'region_id',
        'country',
        'type',
        'trade_type',
        'category_id',
        'status',
        'active'
    ];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'title' => 'required',
        'description' => 'required'
    ];

    /**
     * @var array Relations
     */

    public $attachOne = [
        'video' => \System\Models\File::class
    ];

    public $attachMany = [
        'images'   =>   \System\Models\File::class,
    ];

    public $belongsTo = [
        'user'       => [User::class, 'key' => 'user_id', 'otherKey' => 'id'],
        'region'       => [Region::class, 'key' => 'region_id', 'otherKey' => 'id'],
    ];

    public $statuses = [
        'new'    => 1,
        'processing' => 2,
        'finished'  => 3,
    ];

    public $types = [
        'free'    => 1,
        'vip'     => 2,
    ];

    public $tradeTypes = [
        'sell'    => 1,
        'buy' => 2,
    ];

    public function adverts()
    {
        return $this->morphMany(Advert::class, 'advertable');

    }

    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }

    public function scopePublicVisible($query)
    {
        return $query->active()->where('status', $this->statuses['new']);
    }
}
