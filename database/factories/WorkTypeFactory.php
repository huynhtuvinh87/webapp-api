<?php

use Faker\Generator as Faker;

$factory->define(App\Models\WorkType::class, function (Faker $faker) {
    return [
        'parent_id' => NULL,
        'code' => $faker->numberBetween(999, 100000000),
        'name' => $faker->jobTitle(),
    ];
});
