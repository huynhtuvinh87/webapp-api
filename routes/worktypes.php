<?php

/**
 * WorkType routes
 * @namespace \App\Http\Controllers\Api
 * @prefix /api/work-types
 * @middleware Api, auth:api
 */


//Route::get('/search', 'WorkTypeController@search');


Route::middleware('auth:api')->group(function(){
    Route::middleware('cache')->get('/', 'WorkTypeController@listAll');
    Route::get('/list', 'WorkTypeController@list');
    Route::post('/', 'WorkTypeController@store');
    Route::delete('/{id}', 'WorkTypeController@delete');
    Route::get('/contractors/{workType}', 'WorkTypeController@contractorsByCode');
});

Route::get('/{id?}', 'WorkTypeController@index');
