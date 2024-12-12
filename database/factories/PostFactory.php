<?php

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */

$factory->define(\Coyote\Post::class, function (Faker $faker) {
    return [
        'text'       => $faker->realText(),
        'ip'         => $faker->ipv4,
        'browser'    => $faker->userAgent,
        'created_at' => \Carbon\Carbon::now(),
        'updated_at' => \Carbon\Carbon::now(),
        'user_id'    => factory(\Coyote\User::class),
    ];
});

$factory->state(\Coyote\Post::class, 'id', function (Faker $faker) {
    return [
        'id' => $faker->numberBetween(10000000),
    ];
});

$factory->state(\Coyote\Post::class, 'legacyPostWithoutUser', function () {
    return [
        'user_id'   => null,
        'user_name' => 'legacy',
    ];
});
