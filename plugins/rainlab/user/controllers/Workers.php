<?php namespace RainLab\User\Controllers;

use Cms\Traits\ApiResponser;
use Cms\Traits\Pagination;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Backend\Classes\Controller;
use Illuminate\Support\Facades\DB;
use RainLab\User\Models\User;
use RainLab\User\Models\Worker;
use skillset\Services\Models\ServiceToUser;


class Workers extends Controller
{
    use ApiResponser;
    use Pagination;

    public function index(Request $request, Worker $workerModel)
    {
        return $this->response($workerModel->getAll($request->all()));

    }

}
