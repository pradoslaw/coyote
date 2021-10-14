<?php

use Faker\Generator as Faker;

$factory->define(\Coyote\Comment::class, function (Faker $faker) {
    return [
        'text' => $faker->realText(),
        'parent_id' => null,
        'user_id' => factory(\Coyote\User::class)
    ];
});
