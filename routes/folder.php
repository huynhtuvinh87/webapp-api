<?php
/**
 * Folder routes
 * @namespace \App\Http\Controllers\Api
 * @prefix /api/folders
 * @middleware Api, auth:api
 */

// count HO folders
Route::get('/hiring_organization', 'FolderController@count_hiring_organization_folders');
// upload file to the folder
Route::post('/upload/{folder}', 'FolderController@upload_file');
// read folder content
Route::get('/content/{folder}', 'FolderController@read_content');

Route::post('/{contractor_id}', 'FolderController@create');
Route::get('/{contractor_id}', 'FolderController@read');
Route::post('/edit/{folder}', 'FolderController@update');
Route::post('/delete/{folder}', 'FolderController@delete');
