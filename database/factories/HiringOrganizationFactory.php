<?php

use App\Models\Department;
use App\Models\Facility;
use App\Models\HiringOrganization;
use App\Models\Position;
use Faker\Generator as Faker;

$factory->define(HiringOrganization::class, function (Faker $faker) {
    return [
        "name" => "HO: " . $faker->unique()->company,
        "address" => $faker->streetAddress(),
        "postal_code" => $faker->postcode(),
        "city" => $faker->city(),
        "state" => $faker->state(),
        "country" => $faker->country(),
        "phone" => $faker->phoneNumber(),
        "website" => $faker->url(),
        // "avatar" => $faker->imageUrl(200,200),
        // "logo" => $faker->imageUrl(200,200),
    ];
});

$factory->afterCreating(HiringOrganization::class, function ($hiringOrg, $faker) {
    $department = factory(Department::class)->create([
        'hiring_organization_id' => $hiringOrg->id,
    ]);
    $facility = factory(Facility::class)->create([
        'hiring_organization_id' => $hiringOrg->id,
    ]);
    $employeePosition = factory(Position::class)->create([
        "hiring_organization_id" => $hiringOrg->id,
    ]);
    $contractorPosition = factory(Position::class)->create([
        "hiring_organization_id" => $hiringOrg->id,
    ]);

    // Connecting position to hiring organization
    DB::table('facility_position')->insert([
        'position_id' => $employeePosition->id,
        'facility_id' => $facility->id,
    ]);

    // Connecting position to hiring organization
    DB::table('facility_position')->insert([
        'position_id' => $contractorPosition->id,
        'facility_id' => $facility->id,
    ]);
});
