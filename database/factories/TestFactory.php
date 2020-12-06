<?php

use Faker\Generator as Faker;
use App\Models\Test;

$factory->define(Test::class, function (Faker $faker) {
    return [
        'name' => $faker->name(),
        'html' => $faker->url(),
        'max_tries' => 2,
        'min_passing_criteria' => 1,
    ];
});
