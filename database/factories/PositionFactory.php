<?php

use Faker\Generator as Faker;
use App\Models\Position;

$factory->define(Position::class, function (Faker $faker) {
    $positionTypes = [
        'contractor',
        'employee'
    ];

    return [
        "name" => $faker->jobTitle,
        'is_active' => rand(0,1),
        'hiring_organization_id' => rand(1,125),
        'position_type' => $positionTypes[rand(0, sizeof($positionTypes)-1)]
    ];
});
