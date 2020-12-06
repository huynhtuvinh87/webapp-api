<?php

use Faker\Generator as Faker;

use App\Models\DynamicForm;
use App\Models\DynamicFormColumn;
use App\Models\DynamicFormSubmission;
use App\Models\DynamicFormSubmissionData;

$factory->define(DynamicFormColumn::class, function (Faker $faker) {

    // Picking random type
    $controlTypes = with(new DynamicFormColumn)->getControlTypes();
    $cTypeIndex = array_rand($controlTypes);
    $type = $controlTypes[$cTypeIndex];

    return [
        'label' => $faker->firstName . ' ' . $faker->lastName,
        'description' => $faker->text(250),
        'type' => $type,
        'order' => $faker->numberBetween(0, 1000)
    ];
});
