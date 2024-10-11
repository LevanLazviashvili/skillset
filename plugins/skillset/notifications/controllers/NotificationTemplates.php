<?php namespace skillset\Notifications\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class NotificationTemplates extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('skillset.Notifications', 'main-menu-item');
    }
}
