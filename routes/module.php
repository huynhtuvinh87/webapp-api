<?php

/**
 * Module Visibility routes
 * @namespace \App\Http\Controllers\Api
 * @prefix /api/module
 * @middleware Api, auth:api
 * @controller ModuleController
 */

Route::get('/', 'ModuleController@getModules');

Route::group([
    'prefix' => 'visibility',
], function () {
    Route::get('/role', 'ModuleController@getRoleVisibilities');
    Route::get('/', 'ModuleController@getAllVisibilities');
    Route::get('/{module}', 'ModuleController@getModuleVisibilities');
});
