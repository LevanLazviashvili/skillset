<?php

//use Illuminate\Contracts\Broadcasting\Factory as BroadcastFactory;
//app(BroadcastFactory::class);
//Broadcast::channel('chat', function ($user) {
//    return true;
//});

Route::post('sendmessage', 'skillset\Chat\Controllers\Chat@sendMessage');

