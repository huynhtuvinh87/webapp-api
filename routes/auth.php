<?php

/**
 * Authentication and self-profile routes
 * @namespace \App\Http\Controllers\Api
 * @prefix /api/auth
 * @middleware Api
 */

Route::post('login', 'AuthController@login');
Route::post('forgot-password', 'AuthController@forgotPassword');
Route::post('do-reset', 'AuthController@doReset');

Route::post('register', 'ContractorController@store');

Route::middleware('auth:api')->group(function(){
    Route::get('user', 'AuthController@user');
    Route::get('check', 'AuthController@check');
    Route::post('logout', 'AuthController@logout');
    Route::patch('password', 'AuthController@setPassword');
    Route::post('profile', 'AuthController@updateProfile');
    Route::get('roles', 'AuthController@getRoles');
    Route::patch('role/{role}', 'AuthController@roleChange');
    Route::get('contactable', 'AuthController@contactable');
});
