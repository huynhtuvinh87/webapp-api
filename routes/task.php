<?php

/**
 * Task routes
 * @namespace \App\Http\Controllers\Api
 * @prefix /api/task
 * @middleware Api, auth:api
 */
Route::get('/', 'TaskController@index');
Route::get('assigned-tasks', 'TaskController@assignedTasks');
Route::get('created-tasks', 'TaskController@createdTasks');
Route::get('/types', 'TaskController@types');
Route::get('/{task}', 'TaskController@show');


Route::middleware('isPaid')->group(function(){

    Route::post('/', 'TaskController@store');
    Route::post('/{task}/complete', 'TaskController@complete');
    Route::patch('/{task}/approve', 'TaskController@approve');
    Route::delete('/{task}', 'TaskController@destroy');
    Route::patch('/{task}/reassign/{user_id}', 'TaskController@reassign');

});
