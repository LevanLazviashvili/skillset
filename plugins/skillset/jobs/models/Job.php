<?php namespace skillset\Jobs\Models;

use Illuminate\Support\Arr;
use Model;
use RainLab\User\Models\User;
use skillset\details\Models\Region;
use skillset\orders\models\Advert;

/**
 * Model
 */
class Job extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'skillset_jobs';

    /**
     * @var array The attributes that are mass assignable.
     */
    protected $fillable = [
        'author_role',
        'title',
        'description',
        'price',
        'user_id',
        'region_id',
        'type',
        'status',
        'active',
        'updated_at'
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

    /**
     * @var array Validation rules
     */
    public $rules = [
        'title' => 'required',
        'description' => 'required'
    ];

    public $belongsTo = [
        'user'       => [User::class, 'key' => 'user_id', 'otherKey' => 'id'],
        'region'       => [Region::class, 'key' => 'region_id', 'otherKey' => 'id'],
    ];

    public $statuses = [
        'new'    => 1,
        'in_progress'  => 2,
        'finished'  => 3
    ];

    public $types = [
        'free'    => 1,
        'vip'     => 2,
    ];

    public $authorRoles = [
        'client'     => 1,
        'worker'     => 2,
    ];

    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }

    public function scopePublicVisible($query)
    {
        return $query->active()->where('status', $this->statuses['new']);
    }

    public function getData($params = [])
    {

    }

    public function conversations()
    {
        return $this->hasMany();
    }

    public function adverts()
    {
        return $this->morphMany(Advert::class, 'advertable');

    }
}
