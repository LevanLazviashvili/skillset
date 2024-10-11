<?php namespace skillset\Notifications\Models;

use Model;

/**
 * Model
 */
class NotificationLog extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'skillset_notifications_log';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public function logNotification($UserIDs, $Title, $Body)
    {
        self::create([
            'user_ids'      => json_encode($UserIDs),
            'title'         => $Title,
            'body'          => $Body
        ]);
    }
}
