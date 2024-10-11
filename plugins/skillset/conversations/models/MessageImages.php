<?php
namespace skillset\Conversations\Models;

use Model;

/**
 * Model
 */
class MessageImages extends Model
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
    public $table = 'skillset_conversations_message_images';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
}
