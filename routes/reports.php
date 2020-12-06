<?php

/**
 * Report Routes routes
 * @namespace \App\Http\Controllers\Api
 * @prefix /api/reports
 * @middleware Api, auth:api
 * @controller ReportController
 */

Route::group([
    'prefix' => 'contractor',
    'middleware' => 'isContractorAdmin'
], function(){
    /**
     * @prefix contractor
     * @middleware isContractorAdmin
     */

    Route::get('/pending-exclusions', 'ContractorReportController@pendingExclusions');


});

Route::group([
    'prefix' => 'organization',
    'middleware' => 'isOrganizationAdmin'
], function(){

    // DEV-809 //
    Route::get('/corporate-compliance', 'HiringOrganizationReportController@corporateCompliance');
    Route::get('/employee-compliance', 'HiringOrganizationReportController@employeeCompliance');
    /*
    Route::get('/positions', 'HiringOrganizationReportController@contractorPositions');
    Route::get('/employee-positions', 'HiringOrganizationReportController@employeePositions');
    Route::get('/compliance-by-position', 'HiringOrganizationReportController@complianceByPosition');
    */
    Route::get('/requirements-about-expire', 'HiringOrganizationReportController@requirementsAboutToExpire');
    Route::get('/requirements-past-due', 'HiringOrganizationReportController@requirementsPastDue');

    Route::get('/pending-internal-requirements', 'HiringOrganizationReportController@pendingInternalRequirementsReport');
    Route::get('/pending-employee-invitation', 'HiringOrganizationReportController@pendingEmployeeInvitationReport');

});

Route::get('/', 'ReportController@index');
