<?php

/**
 * Files routes
 * @namespace \App\Http\Controllers\Api
 * @prefix /api/files
 * @middleware Api, auth:api
 * @controller FileController
 */

Route::get('/{file}', 'FileController@read');
Route::post('/', 'FileController@create');
