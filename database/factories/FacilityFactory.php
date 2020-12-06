<?php

use Faker\Generator as Faker;
use App\Models\Facility;

$factory->define(Facility::class, function (Faker $faker) {
    return [
        "name" => $faker->city
    ];
});
