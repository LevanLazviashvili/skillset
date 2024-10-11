<?php

Route::group([ 'prefix' => '{lang}/', 'middleware' => '\Tymon\JWTAuth\Middleware\GetUserFromToken'], function(){
    Route::get('/jobs', 'skillset\Jobs\Controllers\Jobs@get');
    Route::post('/jobs', 'skillset\Jobs\Controllers\Jobs@store');
    Route::get('/jobs/{id}', 'skillset\Jobs\Controllers\Jobs@show');

    Route::post('/jobs/{id}/contact', 'skillset\Jobs\Controllers\Jobs@contact');
    Route::post('/jobs/{id}/renew', 'skillset\Jobs\Controllers\Jobs@renew');
    Route::post('/jobs/{id}/promote', 'skillset\Jobs\Controllers\Jobs@buyVip');

    Route::post('/jobs/offers/services', 'skillset\Jobs\Controllers\Offers@services');
    Route::post('/jobs/offers/{id}/accept', 'skillset\Jobs\Controllers\Offers@acceptOffer');
    Route::post('/jobs/offers/{id}/reject', 'skillset\Jobs\Controllers\Offers@rejectOffer');
    Route::get('/jobs/offers/{id}/services', 'skillset\Jobs\Controllers\Offers@getServices');

    Route::get('/jobs/orders/list', 'skillset\Jobs\Controllers\Orders@get');
    Route::get('/jobs/orders/{id}', 'skillset\Jobs\Controllers\Orders@show');
    Route::post('/jobs/orders/{id}/pay', 'skillset\Jobs\Controllers\Orders@pay');
    Route::post('/jobs/orders/finish-work', 'skillset\Jobs\Controllers\Orders@finishOrderByWorker');
    Route::post('/jobs/orders/accept-work', 'skillset\Jobs\Controllers\Orders@finishOrderByClient');
    Route::get('/jobs/orders/{id}/services', 'skillset\Jobs\Controllers\Orders@getServices');

    Route::prefix('user')->group(function(){
        Route::get('/jobs', 'skillset\Jobs\Controllers\Jobs@userJobs');
    });
});