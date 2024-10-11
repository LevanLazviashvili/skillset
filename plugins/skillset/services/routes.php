<?php
//use skillset\Categories\Controllers\Categories; test sadas
Route::prefix('{lang}/')->group(function(){
    Route::get('/services', 'skillset\Services\Controllers\Services@getAll');
    Route::get('/subservices', 'skillset\Services\Controllers\SubServices@getAll');
    Route::get('/getservicesandsubservices', 'skillset\Services\Controllers\Services@getServicesAndSubServices');


    Route::prefix('user/services')->group(function(){
        Route::get('/', 'skillset\Services\Controllers\Services@getUserServices')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
        Route::post('/', 'skillset\Services\Controllers\Services@addSubServiceToUser')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
        Route::delete('/{id}', 'skillset\Services\Controllers\Services@removeUserSubService')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
//        Route::put('/', 'skillset\Services\Controllers\Services@addSubServiceToUser')->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');
    });
});