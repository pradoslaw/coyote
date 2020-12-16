<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;

$factory->define(\Coyote\Tag::class, function (Faker $faker) {
    $name = $faker->name();

    return [
        'name' => strtolower($name),
        'real_name' => ucfirst($name)
    ];
});
