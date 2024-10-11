<?php namespace skillset\Rating\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Cms\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use skillset\Orders\Models\Order;

class Rating extends Controller
{
    use ApiResponser;
    public $implement = [    ];
    
    public function __construct()
    {
        parent::__construct();
    }

    public function getUserRating(Request $request, \skillset\Rating\Models\Rating $ratingModel)
    {
        $validator = Validator::make($request->all(), [
            'user_id'               => 'required|integer|exists:users,id'
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->getMessageBag(), self::$ERROR_CODES['VALIDATION_ERROR']);
        }
        return $this->response($ratingModel->getAll(array_merge($request->all(), ['with_pagination' => 1])));
    }

    public function rateUser(Request  $request, \skillset\Rating\Models\Rating $ratingModel)
    {
        $validator = Validator::make($request->all(), [
            'order_type'  => 'sometimes|nullable|integer|in:1,2,3', // 1 => 'order', 2 => 'jobOrder', 3 => 'marketplaceOrder
            'order_id'    => 'required|integer',
            'user_id'     => 'required|integer',
            'rate'        => 'required|integer|min:1|max:5'
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->getMessageBag(), self::$ERROR_CODES['VALIDATION_ERROR']);
        }
        return $this->response($ratingModel->rateUser($request->all()));
    }
}
