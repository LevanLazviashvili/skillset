<?php namespace skillset\Orders\Models;

use Model;
use skillset\details\Models\Unit;
use skillset\Services\Models\Service;
use skillset\Services\Models\SubService;

/**
 * Model
 */
class OrderService extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'skillset_orders_services';
    public $timestamps = false;


    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $belongsTo = [
        'UnitName'  => [Unit::class, 'key' => 'unit_id', 'otherKey' => 'id'],
    ];

    public function Services()
    {
        return $this->hasManyThrough(Service::class, SubService::class, 'id', 'id', 'sub_service_id', 'service_id');
    }

    public function SubService()
    {
        return $this->hasOne(SubService::class, 'id', 'sub_service_id');
    }

    public function Unit()
    {
        return $this->hasOne(Unit::class, 'id', 'unit_id');
    }
}
