<?php

use Faker\Generator as Faker;

use App\Models\DynamicForm;
use App\Models\DynamicFormColumn;
use App\Models\DynamicFormSubmission;
use App\Models\DynamicFormSubmissionData;
use App\Models\DynamicFormSubmissionAction;

$factory->define(DynamicFormSubmissionAction::class, function (Faker $faker) {

    // Picking random action
    $actions = with(new DynamicFormSubmissionAction)->getActions();
    $randActionIndex = array_rand($actions);
    $randAction = $actions[$randActionIndex];

    return [
        'action' => $randAction
    ];
});
