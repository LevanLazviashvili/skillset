<?php namespace skillset\Orders\Models;

use Model;

/**
 * Model
 */
class FillBalanceOrder extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'skillset_orders_fillbalance';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
}
