<?php

/**
 * Employee routes
 * @namespace \App\Http\Controllers\Api
 * @prefix /api/employee
 * @middleware Api, auth:api
 */

//self routes

Route::get('overall-compliance', 'EmployeeDashboardController@compliance');

Route::get('company-compliance', 'EmployeeDashboardController@companyCompliance');

Route::get('past-due-requirements', 'EmployeeDashboardController@pastDueRequirements');

Route::get('company-compliance/{id}/requirements', 'EmployeeDashboardController@companyRequirements');

Route::middleware('isPaid')->post('requirements/{id}/aware', 'EmployeeDashboardController@aware');

Route::middleware('isPaid')->post('requirements/{id}/upload', 'EmployeeDashboardController@upload');

Route::middleware('isPaid')->post('requirements/{id}/upload-date', 'EmployeeDashboardController@uploadWithDate');

Route::get('requirements/{id}/history/{role_id}', "EmployeeDashboardController@requirementHistories");

Route::middleware('isPaid')->post('requirements/request-exclusion', "EmployeeDashboardController@requestExclusion");

Route::get('requirements/{requirement}/test', "EmployeeDashboardController@getRequirementTest");

Route::middleware('isPaid')->post('requirements/{requirement}/test', 'EmployeeDashboardController@submitRequirementTest');

Route::get('exclusion-request/{exclusionRequest}', 'EmployeeDashboardController@getExclusionRequest');

Route::middleware('isPaid')->delete('exclusion-request/{exclusionRequest}', 'EmployeeDashboardController@deleteExclusionRequest');

Route::get('requirement/{requirement}/content', 'EmployeeDashboardController@getRequirementContent');

//    Email Verification
Route::get('/{user}/verify-email', "ContractorEmployeeController@sendVerificationEmail");
