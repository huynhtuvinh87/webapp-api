<?php

use Faker\Generator as Faker;

use App\Models\DynamicForm;
use App\Models\DynamicFormColumn;
use App\Models\DynamicFormSubmission;
use App\Models\DynamicFormSubmissionData;

$factory->define(DynamicFormSubmissionData::class, function (Faker $faker) {
    return [
        'dynamic_form_column_label' => $faker->name()
    ];
});
