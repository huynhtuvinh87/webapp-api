<?php

/**
 * Contractor Routes routes
 * @namespace \App\Http\Controllers\Api
 * @prefix /api/contractor
 * @middleware Api, auth:api, isContractorAdmin
 * @controller ContractorController
 */

Route::get('/', 'ContractorController@index');
Route::middleware('isPaid')->post('update', 'ContractorController@update');
Route::middleware('isPaid')->post('logo', 'ContractorController@updateLogo');
Route::get('companies', 'ContractorController@companies');
Route::get('positions', 'ContractorController@positions');
Route::get('facilities', 'ContractorController@facilities');
Route::get('resources', 'ContractorController@resources');
Route::get('positions/hiring-organization/{role_id}', 'ContractorController@employeePositionsByHiringOrganization');
Route::get('positions/hiring-organization/{hiring_organization_id}/{facility_id}', 'ContractorController@employeePositionsByHiringOrganizationByFacility');
Route::get('facilities/hiring-organization/{hiring_organization_id}', 'ContractorController@facilitiesByHiringOrganization');
Route::get('contractor-positions', 'ContractorController@contractorPositions');
Route::get('contractor-positions/hiring-organization/{hiring_organization_id}', 'ContractorController@contractorPositionsByHiringOrganization');
Route::get('resource-positions/hiring-organization/{role_id}', 'ContractorController@resourcePositionsByHiringOrganization');

Route::group([
    'prefix' => 'organization'
], function(){
    Route::get('/', 'ContractorHiringOrganizationController@index');
    Route::get('/search', 'ContractorHiringOrganizationController@search');
    Route::get('/invited', 'ContractorHiringOrganizationController@invites');
    Route::middleware('isPaid')->post('/accept', 'ContractorHiringOrganizationController@acceptInvite');
    Route::middleware('isPaid')->post('/{hiringOrganization}', 'ContractorHiringOrganizationController@addHiringOrganization');
    Route::delete('/{hiringOrganization}', 'ContractorHiringOrganizationController@detach');
});

Route::group([
    "prefix" => "employee"
], function(){
   /**
    * Contractor Employee Routes (Add, Remove, Edit, Assign)
    * @prefix /contractor/employee
    * @controller ContractorEmployeeController
    * @middleware api, auth:api, isContractorAdmin
    */

   Route::get('/', 'ContractorEmployeeController@index');
   Route::middleware('isPaid')->post('/', 'ContractorEmployeeController@store');
   Route::get('/{user}/positions', 'ContractorEmployeeController@positions');
   Route::middleware('isPaid')->post('/{user}/positions', 'ContractorEmployeeController@assignPosition');
   Route::get('/{user}/facilities', 'ContractorEmployeeController@facilities');
   Route::middleware('isPaid')->post('/{user}/facilities', 'ContractorEmployeeController@assignFacility');
   Route::delete('/{user}/positions', 'ContractorEmployeeController@unassignPosition');
   Route::delete('/{user}/facilities', 'ContractorEmployeeController@unassignFacility');
   Route::middleware('isPaid')->post('/{user}', 'ContractorEmployeeController@update');
   //    TODO: Add in facility update
   //Resources
   Route::get('/{role}/resource', 'ContractorEmployeeController@resource');
   Route::middleware('isPaid')->post('/{role}/resource', 'ContractorEmployeeController@assignResource');

   Route::delete('/{user}', 'ContractorEmployeeController@destroy');

});

Route::group([
    "prefix" => "resource"
], function(){
   /**
    * Contractor resource Routes
    * @prefix /contractor/resource
    * @controller ContractorResourceController
    * @middleware api, auth:api, isContractorAdmin
    */

   Route::get('/', 'ContractorResourceController@index');
   Route::post('/', 'ContractorResourceController@store');
   Route::delete('/{resource}', 'ContractorResourceController@destroy');
   //Positions
   Route::get('/{resource}/positions', 'ContractorResourceController@positions');
   Route::middleware('isPaid')->post('/{resource}/positions', 'ContractorResourceController@assignPosition');
   Route::delete('/{resource}/positions', 'ContractorResourceController@unassignPosition');
   //Facilities
   Route::get('/{resource}/facilities', 'ContractorResourceController@facilities');
   Route::middleware('isPaid')->post('/{resource}/facilities', 'ContractorResourceController@assignFacility');
   Route::delete('/{resource}/facilities', 'ContractorResourceController@unassignFacility');
    //Roles
    Route::get('/{resource}/role', 'ContractorResourceController@role');
    Route::middleware('isPaid')->post('/{resource}/role', 'ContractorResourceController@assignRole');
    Route::delete('/{resource}/role', 'ContractorResourceController@unassignRole');
   //Compliance
   Route::get('overall-compliance', 'ContractorResourceController@compliance');
   Route::get('company-compliance', 'ContractorResourceController@companyCompliance');
   //Requirements
   Route::get('company-compliance/{id}/requirements', 'ContractorResourceController@companyRequirements');

   Route::get('/{resource}/past-due-requirements', 'ContractorResourceController@getPastDueRequirements');

});

Route::group([
    "prefix" => "subscription"
], function(){
   /**
    * Contractor Subscription routes
    * @prefix /contractor/subscription
    * @controller ContractorSubscriptionController
    */

    Route::get('/', 'ContractorSubscriptionController@getSubscriptionDetails');
    Route::get('/billing-date', 'ContractorSubscriptionController@getSubscriptionBillingPeriod');

    Route::post('/', 'ContractorSubscriptionController@subscribe');
    Route::post('/coupon/describe', 'ContractorSubscriptionController@testCouponCode');
    Route::post('/coupon/apply', 'ContractorSubscriptionController@applyCouponCode');
    Route::post('/card', 'ContractorSubscriptionController@modifyCreditCard');
    Route::post('/plan', 'ContractorSubscriptionController@modifySubscription');
    Route::delete('/', 'ContractorSubscriptionController@cancel');

    Route::get('/invoice', 'ContractorSubscriptionController@invoiceList');

    Route::get('/invoice/{id}', 'ContractorSubscriptionController@downloadInvoice');

});

Route::group([
    "prefix" => "dashboard"
], function(){
    /**
     * Contractor Dashboard routes
     * @prefix /contractor/dashboard
     * @controller ContractorDashboardController
     */

    //Route::middleware('cache')->get('overall-compliance', 'ContractorDashboardController@compliance');
    Route::get('overall-compliance', 'ContractorDashboardController@compliance');

    Route::middleware('cache')->get('overall-employee-compliance', 'ContractorDashboardController@employeeCompliance');

    //Route::middleware('cache')->get('company-compliance', 'ContractorDashboardController@companyCompliance');
    Route::get('company-compliance', 'ContractorDashboardController@companyCompliance');

    //Route::middleware('cache')->get('past-due-requirements', 'ContractorDashboardController@pastDueRequirements');
    Route::get('past-due-requirements', 'ContractorDashboardController@pastDueRequirements');

    //Route::middleware('cache')->get('company-compliance/{id}/requirements', 'ContractorDashboardController@companyRequirements');
    Route::get('company-compliance/{id}/requirements', 'ContractorDashboardController@companyRequirements');

    Route::middleware('isPaid')->post('requirements/{id}/aware', 'ContractorDashboardController@aware');

    Route::middleware('isPaid')->post('requirements/{id}/upload', 'ContractorDashboardController@upload');

    Route::middleware('isPaid')->post('requirements/{id}/upload-date', 'ContractorDashboardController@uploadWithDate');

    Route::get('requirements/{requirement}/history', "ContractorDashboardController@requirementHistories");

    Route::middleware('isPaid')->post('requirements/request-exclusion', "ContractorDashboardController@requestExclusion");

    Route::get('requirements/{requirement}/test', 'ContractorDashboardController@getRequirementTest');

    Route::middleware('isPaid')->post('requirements/{requirement}/test', 'ContractorDashboardController@submitRequirementTest');

    Route::get('exclusion-request/{exclusionRequest}', 'ContractorDashboardController@getExclusionRequest');

    Route::middleware('isPaid')->delete('exclusion-request/{exclusionRequest}', 'ContractorDashboardController@deleteExclusionRequest');

    Route::get('requirement/{requirement}/content', 'EmployeeDashboardController@getRequirementContent');

    Route::post('answer-subcontractor-survey', 'SubcontractorSurveyController');
});

Route::group([
    "prefix" => "admin"
], function(){
    /**
     * Contractor Admin Routes
     * @prefix /contractor/admin
     * @controller ContractorAdminController
     */
    Route::get('/', 'ContractorAdminController@index');
    Route::middleware('isPaid')->post('/', 'ContractorAdminController@store');
    Route::middleware('isPaid')->post('/{user}', 'ContractorAdminController@update');
    Route::delete('/{user}', 'ContractorAdminController@destroy');

});
