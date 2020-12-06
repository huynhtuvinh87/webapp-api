<?php

/**
 * Translation routes
 * @namespace \App\Http\Controllers\Api
 * @prefix /api/translations
 */
Route::post('/', 'TranslationController@read');
Route::get('/all/{lang}', 'TranslationController@readAll');
