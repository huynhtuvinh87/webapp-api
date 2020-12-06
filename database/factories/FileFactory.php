<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\File;
use Faker\Generator as Faker;

$factory->define(File::class, function (Faker $faker) {
    // NOTE: getting images can be slow
    // $fakerFile = $faker->image('storage/app/public', 640, 480);
    $fakerFile = $faker->file('public/images', 'storage/app/public');
    return [
        'name' => $faker->firstName,
        'path' => $fakerFile
    ];
});
