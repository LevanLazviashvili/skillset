<?php

Route::group([ 'prefix' => '{lang}/posts', 'middleware' => '\Tymon\JWTAuth\Middleware\GetUserFromToken'], function(){
    Route::get('/comments', 'skillset\Forum\Controllers\Comments@get');
    Route::post('/comments', 'Skillset\Forum\Controllers\Comments@store');
    Route::post('/comments/like', 'Skillset\Forum\Controllers\Comments@like');
    Route::get('/comments/{id}', 'skillset\Forum\Controllers\Comments@show');
    Route::delete('/comments/{id}', 'Skillset\Forum\Controllers\Comments@destroy');

    Route::get('/', 'skillset\Forum\Controllers\Posts@get');
    Route::get('/{id}', 'skillset\Forum\Controllers\Posts@show');
    Route::post('/', 'skillset\Forum\Controllers\Posts@store');
    Route::post('/like', 'skillset\Forum\Controllers\Posts@like');
    Route::delete('/{id}', 'skillset\Forum\Controllers\Posts@destroy');
});