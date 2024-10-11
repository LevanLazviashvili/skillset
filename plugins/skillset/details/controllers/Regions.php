<?php namespace skillset\details\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use skillset\details\Models\Country;
use skillset\details\Models\Region;
use Illuminate\Http\Request;

class Regions extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController',        'Backend\Behaviors\ReorderController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('skillset.details', 'main-menu-item', 'side-menu-item2');
    }

    public function getAll(Region $Model, Request $request)
    {
        $Query = $Model->where('status_id', 1)->OrderBy('sort_order');
        if ($CountryID = $request->input('CountryID')){
            $Query->where('country_id', $CountryID);
        }
        return $Query->get();
    }
}
