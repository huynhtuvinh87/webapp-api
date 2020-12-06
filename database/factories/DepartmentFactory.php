<?php

use App\Models\Department;
use Faker\Generator as Faker;

$factory->define(Department::class, function (Faker $faker) {
    return [
        "name" => $faker->unique()->company,
        "description" => $faker->text(200)
    ];
});

$factory->afterCreating(Department::class, function ($department, $faker) {

});
