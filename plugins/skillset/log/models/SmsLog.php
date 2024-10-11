<?php namespace skillset\Log\Models;

use Model;

/**
 * Model
 */
class SmsLog extends Model
{
    use \October\Rain\Database\Traits\Validation;
    public $timestamps = true;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'skillset_log_sms';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
}
