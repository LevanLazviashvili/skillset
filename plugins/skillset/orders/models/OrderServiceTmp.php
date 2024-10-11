<?php namespace skillset\Orders\Models;

use Illuminate\Support\Arr;
use Model;
use skillset\details\Models\Unit;
use skillset\Offers\Models\OfferWorker;

/**
 * Model
 */
class OrderServiceTmp extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'skillset_orders_services_tmp';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $belongsTo = [
        'UnitName'  => [Unit::class, 'key' => 'unit_id', 'otherKey' => 'id'],
    ];

    public function Unit()
    {
        return $this->hasOne(Unit::class, 'id', 'unit_id');
    }

    public function OfferWorker()
    {
        return $this->hasMany(OfferWorker::class, 'offer_id', 'offer_id');
    }

    public function Order()
    {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }

    public function getAll(array $params = [])
    {
        $Obj = self::Query();
        if (Arr::get($params, 'offer_id')) {
            $Obj->where('offer_id', Arr::get($params, 'offer_id'))
                ->whereHas('OfferWorker', function($q) {
                $q->where('worker_id', config('auth.UserID'))->where('status_id', '>=', (new OfferWorker)->statuses['offer_accepted_by_worker']);
            });
        }
        if (Arr::get($params, 'order_id')) {
            $Obj->where('order_id', Arr::get($params, 'order_id'))
                ->whereHas('Order', function ($q) {
                    $q->where('worker_id', config('auth.UserID'));
                });
        }
        return $Obj->get()->toArray();
    }
}
