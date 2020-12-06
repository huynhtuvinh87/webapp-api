<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use App\Models\RequirementHistoryReview;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(RequirementHistoryReview::class, function (Faker $faker) {
    return [
        "created_at" => Carbon::now(),
        "updated_at" => Carbon::now(),
        'status_at' => Carbon::now(),
        'status' => "approved",
    ];
});
