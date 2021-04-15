<?php

use Faker\Generator as Faker;

$factory->define(\Coyote\Post::class, function (Faker $faker) {
    return [
        'text' => $faker->realText(),
        'ip' => $ip = $faker->ipv4,
        'browser' => $faker->userAgent,
        'created_at' => \Carbon\Carbon::now(),
        'updated_at' => \Carbon\Carbon::now(),
        'user_id' => factory(\Coyote\User::class)
    ];
});

$factory->state(\Coyote\Post::class, 'user', function (Faker $faker) {
    return [

    ];
});

$factory->state(\Coyote\Post::class, 'id', function (Faker $faker) {
    return [
        'id' => $faker->numberBetween(10000000)
    ];
});
