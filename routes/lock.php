<?php
/**
 * Lock routes
 * @namespace \App\Http\Controllers\Api
 * @prefix /api/lock
 * @middleware Api, auth:api
 */


Route::post('/', 'LockController@create');
Route::post('/delete', 'LockController@delete');
Route::post('/extend_time', 'LockController@extend_expiration_time');
Route::post('/{hiring_organization}', 'LockController@read');
