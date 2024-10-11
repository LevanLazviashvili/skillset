<?php namespace skillset\Configuration\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Configuration extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('skillset.Configuration', 'main-menu-item');
    }
}
