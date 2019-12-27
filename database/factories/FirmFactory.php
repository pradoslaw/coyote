<?php

use Faker\Generator as Faker;

$factory->define(\Coyote\Firm::class, function (Faker $faker) {
    return [
        'name' => $faker->company,
        'city' => $faker->city,
        'street' => $faker->streetName,
        'latitude' => $faker->latitude,
        'longitude' => $faker->longitude,
        'website' => $faker->url,
        'description' => $faker->text(2000)
    ];
});
