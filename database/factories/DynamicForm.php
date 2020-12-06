<?php

use Faker\Generator as Faker;

use App\Models\DynamicForm;
use App\Models\DynamicFormColumn;
use App\Models\DynamicFormSubmission;
use App\Models\DynamicFormSubmissionData;

$factory->define(DynamicForm::class, function (Faker $faker) {

    // Creating Form
    return [
        'title'=>"Factory Form - " . $faker->catchPhrase,
        'description'=>"Form generated from factory",
    ];
});
