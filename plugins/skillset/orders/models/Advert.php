<?php namespace skillset\orders\models;

use Model;
use skillset\Payments\Models\Payment;

/**
 * Model
 */
class Advert extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'skillset_orders_adverts';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public function advertable()
    {
        return $this->morphTo();
    }

    public function payment()
    {
        $payment = new Payment;

        return $this->hasOne(Payment::class, 'order_id', 'id')
            ->whereIn(
                'payment_type',
                [$payment->paymentTypes['vip_job'], $payment->paymentTypes['vip_marketplace_app']]
            );
    }
}
