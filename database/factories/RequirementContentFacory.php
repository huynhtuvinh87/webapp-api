<?php

use Faker\Generator as Faker;
use App\Models\RequirementContent;

$factory->define(RequirementContent::class, function (Faker $faker) {
    return [
        'lang' => 'en',
        'text' => $faker->sentence(6),
        'file' => $faker->imageURL(640, 480, 'business'),
        'file_name' => $faker->md5(),
        'file_ext'=>'jpg',
        'url' => $faker->imageURL(640, 480, 'cats')
    ];
});
