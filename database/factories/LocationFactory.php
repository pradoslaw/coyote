<?php

use Faker\Generator as Faker;

$factory->define(\Coyote\Job\Location::class, function (Faker $faker) {
    return [
        'city' => $faker->city,
        'street' => $faker->streetAddress,
        'latitude' => $faker->latitude,
        'longitude' => $faker->longitude
    ];
});
