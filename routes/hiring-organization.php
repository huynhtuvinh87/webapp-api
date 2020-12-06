<?php

/**
 * Hiring Organization routes
 * @namespace \App\Http\Controllers\Api
 * @prefix /api/organization
 * @middleware Api, auth:api
 * @controller HiringOrganizationController
 */

Route::get('/', 'HiringOrganizationController@index');
Route::middleware('hasAccess:4')->post('update', 'HiringOrganizationController@update');
Route::middleware('hasAccess:4')->post('logo', 'HiringOrganizationController@updateLogo');

Route::prefix('facility')->group(function(){
    Route::get('/', 'HiringOrganizationFacilityController@index');
    Route::get('/{facility}', 'HiringOrganizationFacilityController@show');
    Route::middleware('hasAccess:4')->post('/', 'HiringOrganizationFacilityController@store');
    Route::middleware('hasAccess:4')->post('/{facility}', 'HiringOrganizationFacilityController@update');
    Route::middleware('hasAccess:4')->delete('/{facility}', 'HiringOrganizationFacilityController@destroy');
});


Route::prefix('resource')->group(function(){
    //Route::middleware('cache')->get('/', 'HiringOrganizationResourceController@index');
    Route::get('/{contractor_id}', 'HiringOrganizationResourceController@index');
    Route::get('/{resource}', 'HiringOrganizationResourceController@show');
    Route::middleware('hasAccess:4')->post('/', 'HiringOrganizationResourceController@store');
    Route::middleware('hasAccess:4')->post('/{facility}', 'HiringOrganizationResourceController@update');
    Route::middleware('hasAccess:4')->delete('/{facility}', 'HiringOrganizationResourceController@destroy');
});

Route::prefix('department')->group(function(){
    Route::get('/', 'HiringOrganizationDepartmentController@index');
    Route::get('/{department}', 'HiringOrganizationDepartmentController@show');
    Route::middleware('hasAccess:4')->post('/', 'HiringOrganizationDepartmentController@store');
    Route::middleware('hasAccess:4')->post('/{department}', 'HiringOrganizationDepartmentController@update');
    Route::middleware('hasAccess:4')->delete('/{department}', 'HiringOrganizationDepartmentController@destroy');

    //DEPRECATED
    Route::middleware('hasAccess:4')->post('/{department}/add-roles', 'HiringOrganizationDepartmentController@addRoles');
    Route::middleware('hasAccess:4')->post('/{department}/remove-roles', 'HiringOrganizationDepartmentController@removeRoles');
    Route::middleware('hasAccess:4')->post('/{department}/add-requirements', 'HiringOrganizationDepartmentController@addRequirements');
    Route::middleware('hasAccess:4')->post('/{department}/remove-requirements', 'HiringOrganizationDepartmentController@removeRequirements');
    //ENDDEPRECATED

});

Route::prefix('position')->group(function(){
    Route::middleware('cache')->get('/', 'HiringOrganizationPositionController@index');
    Route::get('/{position}', 'HiringOrganizationPositionController@show');
    Route::middleware('hasAccess:4')->post('/', 'HiringOrganizationPositionController@store');
    Route::middleware('hasAccess:4')->post('/{position}', 'HiringOrganizationPositionController@update');
    //DEPRECATED
    Route::middleware('hasAccess:4')->delete('/{position}', 'HiringOrganizationPositionController@destroy');
    //ENDDEPRECATED

    Route::middleware('hasAccess:4')->post('/{position}/add-facilities', 'HiringOrganizationPositionController@addFacilities');
    Route::middleware('hasAccess:4')->post('/{position}/remove-facilities', 'HiringOrganizationPositionController@removeFacilities');

    Route::middleware('hasAccess:4')->post('/{position}/add-requirements', 'HiringOrganizationPositionController@addRequirements');
    Route::middleware('hasAccess:4')->post('/{position}/remove-requirements', 'HiringOrganizationPositionController@removeRequirements');
});

Route::prefix('requirement')->group(function(){
    Route::get('/', 'HiringOrganizationRequirementController@index');
    Route::middleware('cache')->get('/{requirement}', 'HiringOrganizationRequirementController@show');
    Route::middleware('hasAccess:4')->post('/', 'HiringOrganizationRequirementController@store');
    Route::middleware('hasAccess:4')->post('/{requirement}', 'HiringOrganizationRequirementController@update');
    Route::middleware('hasAccess:4')->post('/{requirement}/content', 'HiringOrganizationRequirementController@addContent');
    Route::middleware('hasAccess:4')->delete('/{requirement}/content/{requirementContent}', 'HiringOrganizationRequirementController@removeContent');
    Route::middleware('hasAccess:4')->post('/{requirement}/content/{requirementContent}', 'HiringOrganizationRequirementController@updateContent');
    Route::get('/{requirement}/content/list', 'HiringOrganizationRequirementController@getContents');
    Route::get('/{requirement}/content', 'HiringOrganizationRequirementController@getContent');
    Route::middleware('hasAccess:4')->delete('/{requirement}', 'HiringOrganizationRequirementController@destroy');
    Route::middleware('hasAccess:4')->post('/{requirement}/add-departments', 'HiringOrganizationRequirementController@addDepartments');
    Route::middleware('hasAccess:4')->post('/{requirement}/remove-departments', 'HiringOrganizationRequirementController@removeDepartments');
});

Route::prefix('test')->group(function(){
    Route::get('/', 'HiringOrganizationTestController@index');
    Route::get('/{test}', 'HiringOrganizationTestController@show');
    Route::middleware('hasAccess:4')->post('/', 'HiringOrganizationTestController@store');
    Route::middleware('hasAccess:4')->post('/{test}', 'HiringOrganizationTestController@update');
    Route::middleware('hasAccess:4')->delete('/{test}', 'HiringOrganizationTestController@destroy');
    Route::middleware('hasAccess:4')->post('/{test}/question', 'HiringOrganizationTestController@storeQuestion');
    Route::middleware('hasAccess:4')->post('/{test}/question/{question}', 'HiringOrganizationTestController@updateQuestion');
    Route::middleware('hasAccess:4')->delete('/{test}/question/{question}', 'HiringOrganizationTestController@destroyQuestion');
});

Route::prefix('dashboard')->group(function(){
    //Route::middleware('cache')->get('/', 'HiringOrganizationDashboardController@overallCompliance');
    Route::get('/', 'HiringOrganizationDashboardController@overallCompliance');

    Route::get('/contractor/pending-requirements', 'HiringOrganizationDashboardController@pendingContractorRequirements');
    Route::middleware('cache')->get('/contractor/pending-exclusions', 'HiringOrganizationDashboardController@pendingContractorExclusions');
    Route::get('/employee/pending-requirements', 'HiringOrganizationDashboardController@pendingEmployeeRequirements');
    Route::middleware('cache')->get('/employee/pending-exclusions', 'HiringOrganizationDashboardController@pendingEmployeeExclusions');

    Route::get('/warning-internal-requirements', 'HiringOrganizationDashboardController@warningInternalRequirements');
    Route::get('/employee/warning-internal-requirements', 'HiringOrganizationDashboardController@warningEmployeeInternalRequirements');

    Route::middleware('hasAccess:3')->post('/requirement/history/{requirementHistory}/approve',  'HiringOrganizationDashboardController@approveRequirement');
    Route::middleware('hasAccess:3')->post('/requirement/history/{requirementHistory}/decline', 'HiringOrganizationDashboardController@declineRequirement');
    Route::middleware('hasAccess:3')->post('/exclusion/{exclusionRequest}/approve', 'HiringOrganizationDashboardController@approveExclusion');
    Route::middleware('hasAccess:3')->post('/exclusion/{exclusionRequest}/decline', 'HiringOrganizationDashboardController@declineExclusion');

    Route::get('/pending-history/{requirementHistory}/attachment', 'HiringOrganizationDashboardController@getRequirementHistoryAttachment');

    Route::get('/contractor/{contractor}/position', 'HiringOrganizationDashboardController@contractorPositionCompliance');
    Route::get('/contractor/{contractor}/position/{position}', 'HiringOrganizationDashboardController@contractorPositionRequirements');

    Route::get('/contractor/{contractor}/resource', 'HiringOrganizationDashboardController@contractorResourceCompliance');
    Route::get('/contractor/{contractor}/resource/{resource}/position', 'HiringOrganizationDashboardController@contractorResourcePositionCompliance');

    Route::get('/contractor/{contractor}/position/{position}/resource/{resource}', 'HiringOrganizationDashboardController@contractorResourcePositionRequirements');

    Route::get('/contractor/{contractor}/employee', 'HiringOrganizationDashboardController@contractorEmployeeCompliance');
    Route::get('/contractor/{contractor}/employee/{role}/position', 'HiringOrganizationDashboardController@contractorEmployeePositionCompliance');
    Route::get('/contractor/{contractor}/employee/{role}/position/{position}', 'HiringOrganizationDashboardController@contractorEmployeePositionRequirements');

    Route::middleware('hasAccess:2')->post('/requirement/{requirement}/upload', 'HiringOrganizationDashboardController@upload');
    Route::middleware('hasAccess:2')->post('/requirement/{requirement}/upload-with-date', 'HiringOrganizationDashboardController@uploadWithDate');


});

Route::prefix('contractor')->group(function(){
    Route::get('/', 'HiringOrganizationContractorController@index');
    Route::get('/search', 'HiringOrganizationContractorController@search');
    Route::get('/{contractor}', 'HiringOrganizationContractorController@show');
    Route::middleware('hasAccess:4')->post('/{contractor}/rate', 'HiringOrganizationContractorController@rate');
    Route::middleware('hasAccess:4')->delete('/{contractor}', 'HiringOrganizationContractorController@deactivate');
    Route::post('/invite/{contractor}', 'HiringOrganizationContractorController@invite');
    Route::post('/invite/', 'HiringOrganizationContractorController@inviteNew');
    Route::middleware('hasAccess:2')->post('{contractor}/add-facilities', 'HiringOrganizationContractorController@addFacilities');
    Route::middleware('hasAccess:2')->post('{contractor}/remove-facilities', 'HiringOrganizationContractorController@removeFacilities');

    Route::middleware('hasAccess:3')->post('{contractor}/add-resources', 'HiringOrganizationContractorController@addResources');
    Route::middleware('hasAccess:3')->post('{contractor}/remove-resources', 'HiringOrganizationContractorController@removeResources');


    Route::middleware('hasAccess:3')->post('{contractor}/add-positions', 'HiringOrganizationContractorController@addPositions');
    Route::middleware('hasAccess:3')->post('{contractor}/remove-positions', 'HiringOrganizationContractorController@removePositions');
    Route::middleware('hasAccess:2')->post('/employee/{role}/add-positions', 'HiringOrganizationContractorController@addEmployeePositions');
    Route::middleware('hasAccess:2')->post('/employee/{role}/remove-positions', 'HiringOrganizationContractorController@removeEmployeePositions');
    Route::get('/{contractor}/assignable-positions', 'HiringOrganizationContractorController@contractorAssignablePositions');
    Route::get('/{contractor}/assignable-resources', 'HiringOrganizationContractorController@contractorAssignableResources');

    Route::middleware('hasAccess:3')->patch('/external-id', 'HiringOrganizationContractorController@updateExternalId');
    Route::middleware('hasAccess:3')->patch('/employees/external-id', 'HiringOrganizationContractorController@updateContractorEmployeeExternalIds');
    Route::middleware('hasAccess:3')->patch('/resources/external-id', 'HiringOrganizationContractorController@updateResourceExternalIds');

    //Route::get('/{contractor}/assignable-positions', 'HiringOrganizationContractorController@assignablePositions'); //deprecated
    Route::get('/{contractor}/mutually-assigned-facilities', 'HiringOrganizationContractorController@commonFacilities'); //deprecated

});

Route::prefix('admin')->group(function(){
    Route::get('/', 'HiringOrganizationAdminController@index');
    Route::middleware('hasAccess:4')->post('/', 'HiringOrganizationAdminController@store');
    Route::get('/{user}', 'HiringOrganizationAdminController@show');
    Route::middleware('hasAccess:4')->post('/{user}', 'HiringOrganizationAdminController@update');
    Route::middleware('hasAccess:4')->delete('/{user}', 'HiringOrganizationAdminController@destroy');
    Route::prefix('/{user}/department')->group(function(){
        Route::get('/', 'HiringOrganizationAdminController@getDepartments');
        Route::middleware('hasAccess:4')->post('/add', 'HiringOrganizationAdminController@addDepartments');
        Route::middleware('hasAccess:4')->post('/remove', 'HiringOrganizationAdminController@removeDepartments');
    });
    Route::prefix('/{user}/facility')->group(function(){
        Route::get('/', 'HiringOrganizationAdminController@getFacilities');
        Route::middleware('hasAccess:4')->post('/add', 'HiringOrganizationAdminController@addFacilities');
        Route::middleware('hasAccess:4')->post('/remove', 'HiringOrganizationAdminController@removeFacilities');
    });
});
