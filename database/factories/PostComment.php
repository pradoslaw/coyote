<?php

use Faker\Generator as Faker;

$factory->define(\Coyote\Post\Comment::class, function (Faker $faker) {
    return [
        'text' => $faker->realText(),
        'created_at' => \Carbon\Carbon::now(),
        'updated_at' => \Carbon\Carbon::now()
    ];
});
