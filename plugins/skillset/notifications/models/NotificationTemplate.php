<?php namespace skillset\Notifications\Models;

use Model;

/**
 * Model
 */
class NotificationTemplate extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;

    public $implement = ['RainLab.Translate.Behaviors.TranslatableModel'];

    public $translatable = [
        'title', 'description', 'button_title'
    ];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'skillset_notifications_template';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
}
