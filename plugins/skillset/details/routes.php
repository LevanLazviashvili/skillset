<?php
//use skillset\Categories\Controllers\Categories;
Route::prefix('{lang}/')->group(function(){
    Route::get('/details/countries', 'skillset\details\Controllers\Countries@getAll');
    Route::get('/details/regions', 'skillset\details\Controllers\Regions@getAll');
    Route::get('/details/legaltypes', 'skillset\details\Controllers\LegalTypes@getAll');
    Route::get('/details/units', 'skillset\details\Controllers\Units@getAll');
    Route::get('/details/rules', 'skillset\details\Controllers\Texts@getRules');
    Route::get('/details/privacypolicies', 'skillset\details\Controllers\Texts@getPrivacyPolicies');
    Route::get('/details/instructions', 'skillset\details\Controllers\Instructions@getAll');
});