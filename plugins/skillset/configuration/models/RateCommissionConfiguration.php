<?php namespace skillset\Configuration\Models;

use Model;

/**
 * Model
 */
class RateCommissionConfiguration extends Model
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
    public $table = 'skillset_configuration_rate_commission';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
}
