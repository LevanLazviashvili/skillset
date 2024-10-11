<?php namespace skillset\details\Models;

use Model;

/**
 * Model
 */
class Texts extends Model
{
    use \October\Rain\Database\Traits\Validation;
    public $textTypes = [
        'privacy_policies'  => 1,
        'rules'             => 2
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'skillset_details_texts';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];


    public $implement = ['RainLab.Translate.Behaviors.TranslatableModel'];

    public $translatable = ['title', 'description'];

//    public $attachMany = [
//        'images'   =>   \System\Models\File::class,
//    ];

    public function getText($id)
    {
        $TextModel = self::find($id);
        $Data = $TextModel->toArray();
//        $Data['images'] = [];
//        $Data['videos'] = $Data['videos'] ?? [];
//        foreach ($TextModel->images AS $photo) {
//            $Data['images'][] = [
//                'path'    =>   $photo->getPath(),
//                'thumb'   =>   $photo->getThumb(100, 100, ['mode' => 'crop'])
//            ];
//        }
        return $Data;
    }
}
