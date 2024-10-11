<?php namespace skillset\details\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Cms\Traits\ApiResponser;

class Texts extends Controller
{
    use ApiResponser;
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('skillset.details', 'main-menu-item', 'side-menu-item5');
    }

    public function getPrivacyPolicies(\skillset\details\Models\Texts $textModel)
    {
        return $this->response($textModel->getText($textModel->textTypes['privacy_policies']));
    }

    public function getRules(\skillset\details\Models\Texts $textModel)
    {
        return $this->response($textModel->getText($textModel->textTypes['rules']));
    }

    public function getInstructions(\skillset\details\Models\Texts $textModel)
    {
        return $this->response($textModel->getText($textModel->textTypes['instructions']));
    }
}
