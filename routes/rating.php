<?php
/**
 * Rating routes
 * @namespace \App\Http\Controllers\Api
 * @prefix /api/ratings
 * @middleware Api, auth:api
 */

Route::post('/{contractor}', 'RatingController@store');
Route::get('/{contractor}', 'RatingController@read');
