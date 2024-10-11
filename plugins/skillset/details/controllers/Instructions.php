<?php namespace skillset\details\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Cms\Traits\ApiResponser;
use Illuminate\Http\Request;
use October\Rain\Support\Arr;
use skillset\details\Models\Instruction;

class Instructions extends Controller
{
    use ApiResponser;
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController',        'Backend\Behaviors\ReorderController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    public $requiredPermissions = [
        'details' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('skillset.details', 'main-menu-item', 'side-menu-item6');
    }

    public function formAfterSave(Instruction $Instruction)
    {
        $url = $Instruction->video_url;
        $videoID = Arr::get(explode("v=", $url),1) ?? Arr::get(explode("youtu.be/", $url), 1);
        $videoID = explode('&', $videoID)[0];
        $Instruction->thumb = 'http://img.youtube.com/vi/'.$videoID.'/0.jpg';
        $Instruction->save();

    }

    public function getAll(Instruction $Model, Request $request)
    {
        return $this->response($Model->where('status_id', 1)->OrderBy('sort_order')->get()->toArray());
    }


}
