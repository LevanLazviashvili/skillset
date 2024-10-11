<?php namespace skillset\Services\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Cms\Traits\ApiResponser;
use Illuminate\Http\Request;
use skillset\Services\Models\SubService;

class SubServices extends Controller
{
    use ApiResponser;
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController',        'Backend\Behaviors\ReorderController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('skillset.Services', 'main-menu-item', 'side-menu-item2');
    }

    /**
     * returns sub services by param service_id
     * @param Request $request
     * service_id
     * @return mixed
     */
    public function getAll(Request $request, SubService $subServiceModel)
    {
        return $this->response($subServiceModel->getAll($request->all()));
    }
}
