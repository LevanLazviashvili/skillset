<?php namespace skillset\details\Models;

use Model;

/**
 * Model
 */
class Instruction extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\Sortable;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'skillset_details_instructions';
    protected $visible = ['id', 'title', 'video_url', 'thumb'];

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $implement = ['RainLab.Translate.Behaviors.TranslatableModel'];

    public $translatable = ['title'];
}
