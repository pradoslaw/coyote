<?php

use Faker\Generator as Faker;

$factory->define(\Coyote\Post::class, function (Faker $faker) {
    return [
        'text' => $faker->realText(),
        'ip' => $ip = $faker->ipv4,
        'browser' => $faker->userAgent,
        'user_name' => $faker->userName,
        'created_at' => \Carbon\Carbon::now(),
        'updated_at' => \Carbon\Carbon::now()
    ];
});

$factory->state(\Coyote\Post::class, 'user', function (Faker $faker) {
    return [
        'user_id' => factory(\Coyote\User::class)->create()
    ];
});

$factory->state(\Coyote\Post::class, 'id', function (Faker $faker) {
    return [
        'id' => $faker->numberBetween(10000000)
    ];
});
