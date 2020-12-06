<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Contractor::class, function (Faker $faker) {
    $data = [
        "name" => $faker->unique()->company,
        // TODO: Convert to an actual user's id
        "owner_id" => $faker->numberBetween(1, 100),
    ];

    $contractorHasStripeCustomerId = $faker->boolean(90);
    // Can't setup the subscription information through here atm
    // Don't know how to call methods after this factory has generated the contractor
    // $contractorHasStripeSubscriptionId = $faker->boolean();

    if($contractorHasStripeCustomerId){
        $data['stripe_id'] = $faker->regexify('cus\_[\w]{14}');
    }

    return $data;
});
