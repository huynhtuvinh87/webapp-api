<?php

/**
 * Notifications Routes routes
 * @namespace \App\Http\Controllers\Api
 * @prefix /api/notifications
 * @middleware Api, auth:api
 * @controller NotificationController
 */

Route::get('/', 'SystemNotificationController@index');
Route::get('/new', 'SystemNotificationController@unread');
Route::get('/{notification}', 'SystemNotificationController@show');
Route::post('/dismiss', 'SystemNotificationController@readAll');
Route::post('/send', 'SystemNotificationController@notify');
