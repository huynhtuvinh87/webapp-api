<?php

use Faker\Generator as Faker;
use App\Models\RequirementContent;

$factory->define(RequirementContent::class, function (Faker $faker) {
    $langs = [
        'en',
        'fr',
        'es'
    ];

    $fakeUrl = $faker->url();
    $fakeText = $faker->sentence(10);

    return [
        'lang' => $faker->randomElement($langs),
        'text' => $fakeText,
        'url' => $fakeUrl,
        'name' => $faker->sentence(4),
        'description' => $faker->sentence(10),
        // 'file',
        // 'file_name',
        // 'file_ext',
        // 'requirement_id' => $requirement['id']
    ];
});
