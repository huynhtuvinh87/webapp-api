<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\RequirementHistory;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(RequirementHistory::class, function (Faker $faker) {
    return [
        'completion_date' => Carbon::now()
    ];
});
