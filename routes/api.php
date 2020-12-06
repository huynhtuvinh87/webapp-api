<?php

/**
 * Route Notes:
 * Modules are separated into files.
 *
 * api/{prefix}/ = /routes/{prefix}.php
 *
 * @example /api/auth/user is in auth.php
 *
 * Routes in this file are generally unauthenticated, global, misfit routes
 */

Route::get('email/resend/{id}', 'Auth\VerificationController@resend')->name('verification.resend');
Route::get('email/send-verification/{id}', 'Auth\VerificationController@sendVerification')->name('verification.send-verification');
Route::get('email/verify/{id}', 'Auth\VerificationController@verify')->name('verification.email');

Route::middleware('api')->group(function(){
    Route::post('/user/sign_tcs', 'Api\ApiController@signTcs');

    Route::get('search/hiring-organizations', 'Api\ApiController@searchHiringOrganizations');
    Route::get('/hiring-organizations/{hiringOrganization}/facilities', 'Api\ApiController@getHiringOrganizationFacilities');
    Route::get('search/contractors', 'Api\ApiController@searchContractors');

    Route::post(
        'stripe/webhook',
        '\Laravel\Cashier\Http\Controllers\WebhookController@handleWebhook'
    );

    Route::post(
        'stripe/custom-webhook',
        'Api\ApiController@stripeCustomWebhook'
    );

    Route::post('coupon/describe', 'Api\ContractorSubscriptionController@testCouponCode');

    Route::post('invitation/describe', 'Api\ContractorHiringOrganizationController@describeInvitation');

    Route::middleware('auth:api')->group(function(){
        Route::get('contactable/company', 'Api\ApiController@contactableCompanies');
        Route::middleware('cache')->get('contactable/user', 'Api\ApiController@contactableUsers');
        Route::post('/user/{id}/sign_tcs', 'Api\ApiController@signTcs');
    });

    Route::middleware('cache')->get('stripe/plans', 'Api\ApiController@describePlans');

    Route::get('invite/search', 'Api\ApiController@searchInvite');

});
