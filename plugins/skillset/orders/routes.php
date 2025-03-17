<?php
//use skillset\Categories\Controllers\Categories; 111a2aხ
use Carbon\Carbon;
use Illuminate\Support\Arr;
use skillset\Conversations\Models\Message;
use skillset\Notifications\Models\Notification;

Route::prefix('{lang}/user')->group(function(){
    Route::get('getoffers', 'skillset\Offers\Controllers\Offers@getAll')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
    Route::get('getoffer/{id}', 'skillset\Offers\Controllers\Offers@getOne')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
    Route::post('makeoffer', 'skillset\Offers\Controllers\Offers@makeOffer')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
    Route::post('updateoffer', 'skillset\Offers\Controllers\Offers@updateOffer')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
    Route::get('getofferservices', 'skillset\Offers\Controllers\Offers@getOfferServices')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
    Route::post('editofferservices', 'skillset\Offers\Controllers\Offers@editOfferServices')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
    Route::get('/orders', 'skillset\Orders\Controllers\Orders@getAll')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
    Route::get('/order/{id}', 'skillset\Orders\Controllers\Orders@getOne')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
    Route::get('/order', 'skillset\Orders\Controllers\Orders@getOne')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
    Route::get('/ordersandoffers', 'skillset\Orders\Controllers\Orders@getOrdersAndOffers')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
    Route::post('/createorder', 'skillset\Orders\Controllers\Orders@createOrder')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
    Route::post('/updateorder', 'skillset\Orders\Controllers\Orders@updateOrder')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
    Route::post('/finishorderbyworker', 'skillset\Orders\Controllers\Orders@finishOrderByWorker')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
    Route::post('/finishorderbyuser', 'skillset\Orders\Controllers\Orders@finishOrderByUser')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');

    Route::post('/rateuser', 'skillset\Rating\Controllers\Rating@rateUser')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
    Route::get('/getconversationdetails', 'skillset\Conversations\Controllers\Conversations@getDetails')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
    Route::get('/getconversations', 'skillset\Conversations\Controllers\Conversations@getConversations')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
    Route::post('/uploadconversationimages', 'skillset\Conversations\Controllers\Conversations@uploadImages')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
    Route::post('/supportconversation', 'skillset\Conversations\Controllers\Conversations@startConversationWithSupport')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
    Route::get('/hassupportconversation', 'skillset\Conversations\Controllers\Conversations@hasActiveSupportConversation')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
    Route::get('/hasnewconversation', 'skillset\Conversations\Controllers\Conversations@userHasNewConversations')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
    Route::post('/supportuserconversation', 'skillset\Conversations\Controllers\Conversations@startConversationWithSupportUser')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');

    Route::get('/orderupdates', 'skillset\Orders\Controllers\Orders@userHasOrderUpdates')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
});
Route::prefix('{lang}/payments/')->group(function() {
//    Route::get('/order', 'skillset\Payments\Controllers\Payments@paymentOrder')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
    Route::get('/status', 'skillset\Payments\Controllers\Payments@getPaymentStatus')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
    Route::post('/paymentcallback', 'skillset\Payments\Controllers\Payments@paymentCallback');
    Route::post('/refundcallback', 'skillset\Payments\Controllers\Payments@refundCallback');
});

Route::post('{lang}/sendmessagenotification', 'skillset\Notifications\Controllers\Notifications@sendMessageNotification')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
Route::get('unreadmessages', 'skillset\Conversations\Controllers\Conversations@AdminUnreadMessagesCount');
Route::get('getofferworkers', 'skillset\Offers\Controllers\Offers@adminGetOfferWorkers');

//Route::post('{lang}/test', 'skillset\Notifications\Controllers\Notifications@test');

Route::get('test', function() {
//    echo 'new';
//    (new Notification)->sendTemplateNotifications([209], 'unPaidOrder', [],['type' => 'order', 'id' => 123], 'order_details');
//    (new \skillset\Notifications\Models\Notification)->sendTemplateNotificationsByUserIDs([43,20], 'newOffer');
    (new Notification())->sendTemplateNotifications(
        [209],
        'newJob',
        [],
        ['type' => 'job', 'id' => 1],
        'job_details',
        'new_job_' . 1
    );
});

Route::get('errorlog', function () {
//    if ($_SERVER['REMOTE_ADDR'] == '188.123.138.96') {
        $file = storage_path('logs/system.log');
        if (file_exists($file)) {
            print_R(file_get_contents($file));
        }
//    }
});

Route::get('clearerrorlog', function () {
//    if ($_SERVER['REMOTE_ADDR'] == '188.123.138.96') {
        $file = storage_path('logs/system.log');
        if (file_exists($file)) {
            $file=fopen($file,"w");
            fwrite($file, '');
            fclose($file);
        }
//    }
});