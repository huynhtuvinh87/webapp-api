<?php

use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(\App\Models\Resource::class, function (Faker $faker) {
    $data = [
        "name" => $faker->unique()->company,
        "created_at" => Carbon::now(),
        "updated_at" => Carbon::now(),
    ];

    return $data;
});
