<?php

use RainLab\User\Models\User as UserModel;
use Vdomah\JWTAuth\Models\Settings;


//test1
Route::prefix('{lang}/')->group(function() {
    Route::get('payments/response', 'skillset\Payments\Controllers\Payments@paymentPage');

    Route::post('signup', 'RainLab\User\Controllers\Users@signUp');
    Route::post('login', 'RainLab\User\Controllers\Users@login');
    Route::post('refresh', 'RainLab\User\Controllers\Users@refreshToken');
    Route::post('logout', 'RainLab\User\Controllers\Users@logout')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
    Route::post('sendresetpasswordcode', 'RainLab\User\Controllers\Users@sendResetPasswordCode');
    Route::post('resetpassword', 'RainLab\User\Controllers\Users@resetpassword');

    Route::prefix('user')->group(function() {
        Route::get('/', 'RainLab\User\Controllers\Users@getUserInfo')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
        Route::get('/get/{id}', 'RainLab\User\Controllers\Users@getUserInfo')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
        Route::post('edit', 'RainLab\User\Controllers\Users@edit')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
        Route::post('updatelang', 'RainLab\User\Controllers\Users@updateUserLang')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
        Route::get('getgallery', 'RainLab\User\Controllers\Users@getGallery')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
        Route::post('updategallery', 'RainLab\User\Controllers\Users@updateGallery')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
        Route::post('changestatus', 'RainLab\User\Controllers\Users@changeStatus')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
        Route::post('delete', 'RainLab\User\Controllers\Users@deleteMyUser')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
        Route::post('sendverificationcode', 'RainLab\User\Controllers\Users@sendVerificationCode');
        Route::post('checkverificationcode', 'RainLab\User\Controllers\Users@checkVerificationCode');
        Route::get('getrates', 'skillset\Rating\Controllers\Rating@getUserRating');

        Route::post('savedevicetoken', 'RainLab\User\Controllers\Users@saveDeviceToken')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');

        Route::post('fillbalance', 'RainLab\User\Controllers\Users@fillBalance')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');

        Route::get('unread-counts', 'RainLab\User\Controllers\Users@getUnreadCounts')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
        Route::put('bank-account-number', 'RainLab\User\Controllers\Users@updateBankAccountNumber')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
        Route::get('notification-statuses', 'RainLab\User\Controllers\Users@getUserNotificationStatuses')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
        Route::post('notification-statuses', 'RainLab\User\Controllers\Users@updateNotificationStatuses')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
    });
    Route::post('sendtestnotification', 'RainLab\User\Controllers\Users@sendTestPushNotification');

    Route::get('workers', 'RainLab\User\Controllers\Workers@index');
    Route::get('getnotifications', 'skillset\Notifications\Controllers\Notifications@getNotifications')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
    Route::post('seennotification', 'skillset\Notifications\Controllers\Notifications@seenNotification')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');



    Route::post('sendautonotifications', 'skillset\Notifications\Controllers\Notifications@sendAutoNotifications');
    Route::post('sendtestnotification', 'skillset\Notifications\Controllers\Notifications@sendTestNotification');

});

