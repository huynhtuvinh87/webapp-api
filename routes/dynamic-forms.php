<?php

/**
 * Dynamic Form routes
 * @namespace \App\Http\Controllers\Api
 * @prefix /api/forms
 *
 * TODO: Wrap everything in the auth api middleware
 */


Route::middleware('auth:api')->group(function(){
    // ===== Form CRUD ===== //
    // TODO: Put into group: /form
    // Create Forms
    Route::post('form/create', 'DynamicFormController@createForm');
    // Read Forms
    Route::get('form/read/{dynamicForm}', 'DynamicFormController@readForm');
    // Update Forms
    Route::post('form/update/{originalForm}', 'DynamicFormController@updateForm');
    // Delete Forms
    Route::delete('form/delete/{dynamicForm}', 'DynamicFormController@deleteForm');

    // Create Column
    Route::post('form/{dynamicForm}/column/create', 'DynamicFormController@createColumn');

    // ===== Submission CRUD ===== //
    // TODO: Put into group: submission/
    // Create Submission Data
    Route::middleware('hasAccess:3')->post(
        'submission/create/{requirement}',
        'DynamicFormSubmissionController@createSubmission'
    );
    // Read Submission Data
    Route::get(
        'submission/read/{dynamicFormSubmission}',
        'DynamicFormSubmissionController@readSubmission'
    );
    Route::get(
        'submission/read/requirement_history/{requirementHistory}',
        'DynamicFormSubmissionController@readSubmissionByRequirementHistory'
    );
    // Updating Submissions
    Route::middleware('hasAccess:3')->post(
        'submission/update/{originalSubmission}',
        'DynamicFormSubmissionController@updateSubmission'
    );
    // Deleting Submissions
    Route::middleware('hasAccess:3')->delete(
        'submission/delete/{dynamicFormSubmission}',
        'DynamicFormSubmissionController@deleteSubmission'
    );

    // ===== Special Cases ===== //
    // Read all submissions from form
    Route::get('form/submissions/{dynamicForm}', 'DynamicFormSubmissionController@getAllSubmissions');

    Route::get('form/get', 'DynamicFormController@getForms');

    // Apply Actions
    Route::get('submission/actions/run/{dynamicFormSubmission}', 'DynamicFormSubmissionController@runActions');


    // Get form by requirement ID
    Route::get('form/read/requirement/{requirement}', 'DynamicFormController@readFormByRequirement');
});
