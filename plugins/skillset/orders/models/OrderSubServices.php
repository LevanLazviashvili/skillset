<?php namespace skillset\Orders\Models;

use Model;
use skillset\Services\Models\Service;
use skillset\Services\Models\SubService;

/**
 * Model
 */
class OrderSubServices extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'skillset_orders_sub_services';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public function Services()
    {
        return $this->hasManyThrough(Service::class, SubService::class, 'id', 'id', 'sub_service_id', 'service_id');
    }

    public function SubService()
    {
        return $this->hasOne(SubService::class, 'id', 'sub_service_id');
    }
}
