<?php

use Faker\Generator as Faker;

$factory->define(\Coyote\Post::class, function (Faker $faker) {
    return [
        'text' => $faker->realText(),
        'ip' => $ip = $faker->ipv4,
        'host' => $ip,
        'browser' => $faker->userAgent,
        'user_name' => $faker->userName,
        'created_at' => \Carbon\Carbon::now(),
        'updated_at' => \Carbon\Carbon::now()
    ];
});
