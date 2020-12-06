<?php

use Faker\Generator as Faker;
use App\Models\ModuleVisibility;

$factory->define(ModuleVisibility::class, function (Faker $faker) {
    return [
        'visible' => $faker->boolean()
    ];
});
