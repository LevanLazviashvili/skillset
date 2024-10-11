<?php namespace skillset\details\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use skillset\details\Models\Country;
use Illuminate\Http\Request;

class Countries extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController',        'Backend\Behaviors\ReorderController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('skillset.details', 'main-menu-item', 'side-menu-item');
    }

    public function getAll(Country $Model, Request $request)
    {
        return $Model->where('status_id', 1)->OrderBy('sort_order')->get();
    }
}
