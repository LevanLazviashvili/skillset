<?php namespace Skillset\Forum\Models;

use Model;

/**
 * Model
 */
class Like extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'skillset_forum_likes';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public function likeable()
    {
        return $this->morphTo();
    }
}
