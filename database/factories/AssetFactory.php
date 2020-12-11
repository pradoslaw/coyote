<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use Faker\Generator as Faker;

$factory->define(\Coyote\Models\Asset::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'path' => 'b.png',
        'size' => $faker->randomDigit
    ];
});
