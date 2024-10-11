<?php namespace skillset\Services\Models;

use Illuminate\Support\Arr;
use Model;

/**
 * Model
 */
class Service extends Model
{
    use \October\Rain\Database\Traits\Validation;


    use \October\Rain\Database\Traits\NestedTree;
    /**
     * @var string The database table used by the model.
     */
    public $table = 'skillset_services_';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    protected $visible = ['id','title', 'SubServices'];

    public $implement = ['RainLab.Translate.Behaviors.TranslatableModel'];

    public $translatable = [
        'title'
    ];

    public function SubServices()
    {
        return $this->hasMany(SubService::class, 'service_id', 'id')->where('default', 1);
    }

    public function beforeSave()
    {
        $RequestData = \request()->all();
        foreach (Arr::get($RequestData, 'RLTranslate') AS $item) {
            foreach ($this->translatable AS $field) {
                if (Arr::get($item, $field)) {
                    $this->slug .= Arr::get($item, $field) . '.';
                }
            }
        }
        $this->slug = substr($this->slug, 0, -1);
    }

    public function getAll($params = [])
    {
        $Query = self::where('status_id', config('app.statuses.active'));
        if (Arr::get($params, 'keyword')) {
            $Query->Where('slug', 'like', '%'.Arr::get($params,'keyword').'%');
        }
        return $Query->OrderBy('nest_left')->get();
    }
}
