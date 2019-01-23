<?php

use Faker\Generator as Faker;

$factory->define(App\Club::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'phone' => $faker->phoneNumber,
        'physical_address' => $faker->address,
        'postal_address' => $faker->postcode,
        'latlong' => $faker->latitude.','.$faker->longitude,
    ];
});
