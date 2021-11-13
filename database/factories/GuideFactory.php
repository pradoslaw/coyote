<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use Faker\Generator as Faker;

$factory->define(\Coyote\Guide::class, function (Faker $faker) {
    return [
        'title' => $faker->text,
        'excerpt' => $faker->text,
        'text' => $faker->text,
        'user_id' => factory(\Coyote\User::class)
    ];
});

$factory->state(\Coyote\Guide::class, 'id', function (Faker $faker) {
    return [
        'id' => $faker->numberBetween(10000000)
    ];
});
