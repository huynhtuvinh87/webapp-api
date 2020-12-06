<?php

use Faker\Generator as Faker;
use App\Models\Module;

$factory->define(Module::class, function (Faker $faker) {
    return [
        'name' => $faker->slug(),
        'visible' => $faker->boolean()
    ];
});
