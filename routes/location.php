<?php

/**
 * Location routes
 * @namespace \App\Http\Controllers\Api
 * @prefix /api/location
 * @middleware Api, auth:api
 */

Route::middleware('cache')->get('countries/{id?}', 'LocationController@countries');
Route::middleware('cache')->get('state/{id?}', 'LocationController@state');
