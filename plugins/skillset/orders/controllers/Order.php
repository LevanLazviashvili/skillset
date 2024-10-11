<?php namespace skillset\Orders\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Order extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController'    ];
    
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('skillset.Orders', 'main-menu-item');
    }
}
