<?php

Route::group([ 'prefix' => '{lang}/', 'middleware' => '\Tymon\JWTAuth\Middleware\GetUserFromToken'], function(){
    Route::prefix('marketplace')->group(function(){
        Route::get('/applications', 'skillset\Marketplace\Controllers\Applications@get');
        Route::post('/applications', 'skillset\Marketplace\Controllers\Applications@store');
        Route::get('/applications/{id}', 'skillset\Marketplace\Controllers\Applications@show');
        Route::post('/applications/{id}/promote', 'skillset\Marketplace\Controllers\Applications@buyVip');
        Route::post('/applications/{id}/contact', 'skillset\Marketplace\Controllers\Applications@contact');

        Route::post('/offers/products', 'skillset\Marketplace\Controllers\Offers@products');
        Route::post('/offers/{id}/accept', 'skillset\Marketplace\Controllers\Offers@accept');
        Route::post('/offers/{id}/reject', 'skillset\Marketplace\Controllers\Offers@reject');
        Route::get('/offers/{id}/products', 'skillset\Marketplace\Controllers\Offers@getProducts');

        Route::post('/orders/complete-purchase', 'skillset\Marketplace\Controllers\Orders@finishOrderByClient');
        Route::post('/orders/finish', 'skillset\Marketplace\Controllers\Orders@finishOrderBySeller');
        Route::post('/orders/{id}/pay', 'skillset\Marketplace\Controllers\Orders@pay');
        Route::get('/orders/{id}/products', 'skillset\Marketplace\Controllers\Orders@getProducts');
        Route::get('/orders/list', 'skillset\Marketplace\Controllers\Orders@get');
        Route::get('/orders/{id}', 'skillset\Marketplace\Controllers\Orders@show');
    });

    Route::prefix('user/marketplace')->group(function(){
        Route::get('/applications', 'skillset\Marketplace\Controllers\Applications@userApplications');
    });
});