<?php

use Faker\Generator as Faker;

use App\Models\DynamicForm;
use App\Models\DynamicFormColumn;
use App\Models\DynamicFormSubmission;
use App\Models\DynamicFormSubmissionData;

$factory->define(DynamicFormSubmission::class, function (Faker $faker) {
    return [
        'create_role_id' => 1,
    ];
});

/**
 * Takes in the `dynamic_form_model` property, and uses that to create a submission
 */
$factory->afterCreating(DynamicFormSubmission::class, function ($submission, $faker) {
    // Getting form and column data from submission
    $model = json_decode($submission->dynamic_form_model);
    if( !isset($model) ){
        throw new Exception("Model was not defined. " . $model);
    }

    $form = new DynamicForm((array) $model->form);
    $form = $submission->storedDynamicForm();

    if(
        !isset($form->columns) ||
        sizeof($form->columns) == 0
        ){
            // throw new Exception("No columns were found when creating submission data for form " . $form->id . " \n" . $submission->dynamic_form_model . "\n");
    }

    // Creating submission data entries for each column
    foreach($form->columns as $key => $column){
        // Creating column object
        // $column = new DynamicForm((array) $columnData);

        // Determining value based on column type
        $value = "test";

        if(!isset($column)){
            throw new Exception("Column was undefined");
        }
        if(!isset($column->label)){
            throw new Exception("Column label was not defined. " . json_encode($form->columns));
        }

        // Creating Submission Data object
        $submissionData = factory(DynamicFormSubmissionData::class)->create([
            'dynamic_form_submission_id' => $submission->id,
            'dynamic_form_column_label' => $column->label,
            'value' => $value
        ]);
    }
});
