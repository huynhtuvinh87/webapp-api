<?php

use App\Models\Requirement;
use Faker\Generator as Faker;

$factory->define(Requirement::class, function (Faker $faker) {
    $selectedRequirementType = null;
    $selectedContentType = null;

    $availableRequirementTypes = with(new Requirement)->getTypes();
    $availableContentTypes = with(new Requirement)->getContentTypes();

    $selectedRequirementType = $faker->randomElement($availableRequirementTypes);
    // Setting content type to not include file as file has not been set up to automatically generate
    $selectedContentType = $faker->randomElement(['text', 'url']);

    return [
        'type' => $selectedRequirementType,
        'warning_period' => $faker->numberBetween(1, 100),
        'renewal_period' => $faker->numberBetween(1, 100),
        'count_if_not_approved' => $faker->numberBetween(0, 1),
        'notification_email' => null,
        'content_type' => $selectedContentType,
        'description' => "Automatically generated requirement from the requirement factory",
    ];
});
