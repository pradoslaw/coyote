<?php

use Faker\Generator as Faker;

$factory->define(\Coyote\User::class, function (Faker $faker) {
    return [
        'name' => $faker->userName,
        'email' => $faker->unique()->safeEmail,
        'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm',
        'is_confirm' => true,
        'alert_login' => true,
        'guest_id'  => $faker->uuid
    ];
});
