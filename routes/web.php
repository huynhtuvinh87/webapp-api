<?php

/**
 * Administration routes. The web application will be used specifically for CC Administrators
 * for concierge services.
 *
 * These routes should be as protected as possible. Auth with the "ccadmin" role at a minimum. IP Whitelisting
 * may be required in the future
 */

Route::get('/', 'HomeController@index');
Route::get('/phpinfo', 'HomeController@phpinfo');

Route::get('/company/checkCompliance/', 'Api\IntegrationController@tractionGuest');


Route::group([
    "prefix" => "admin",
], function(){

    //authentication routes are the only non-protected routes

    Auth::routes(['register' => 'false']);

    Route::group([
        "middleware" => [
            "auth",
            "globalAdmin"
        ]
    ], function(){
        Route::get('/', 'HomeController@index')->name('home');

        //user management
        Route::get('/users', 'Admin\UserController@index');
        Route::get('/users/{id}', 'Admin\UserController@edit');
        Route::patch('/users/{id}', 'Admin\UserController@update');

        Route::get('/users/assume/{id}', 'Admin\UserController@assume');
        Route::get('/users/{id}/clear-cache', 'Admin\UserController@clearCache');

        //Route::post('/users/make-admin/{id}', 'Admin\UserController@makeAdmin');
        //Route::post('/users/revoke-admin/{id}', 'Admin\UserController@revokeAdmin');

        //data exports
        Route::get('/exports', 'Admin\ExportController@index');
        Route::get('/exports/users', 'Admin\ExportController@users');

        //user group management
        Route::get('/hiring-org', 'Admin\HiringOrganizationController@index');
        Route::post('/hiring-org', 'Admin\HiringOrganizationController@store');
        Route::get('/hiring-org/{id}', 'Admin\HiringOrganizationController@show');
        Route::post('/hiring-org/{hiring_organization}/status', 'Admin\HiringOrganizationController@toggleIsActive');
        Route::get('/hiring-org/{id}/clear-cache', 'Admin\HiringOrganizationController@clearCache');

        Route::get('/contractor', 'Admin\ContractorController@index');
        Route::get('/contractor/{id}', 'Admin\ContractorController@show');
        Route::get('/contractor/{id}/compliance', 'Admin\ContractorController@compliance');
        Route::get('/contractor/{id}/update-stripe', 'Admin\ContractorController@updateStripe');
        Route::post('/contractor/{contractor}/status', 'Admin\ContractorController@toggleIsActive');
        Route::get('/contractor/{id}/clear-cache', 'Admin\ContractorController@clearCache');


        //get list of pending invites
        Route::get('/contractor-invites', 'Admin\InviteController@show');
        Route::get('/invites', 'Admin\InviteController@index');

        // Modules management
        Route::group([
            "prefix" => "modules",
        ], function(){
            Route::get('/', "Admin\ModuleController@index");
            Route::get('/{id}', "Admin\ModuleController@show");
            Route::get('/{id}/visibilities', "Admin\ModuleController@visibilities");
        });

    });

});
