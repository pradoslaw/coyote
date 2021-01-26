<?php

use Faker\Generator as Faker;

$factory->define(\Coyote\Microblog::class, function (Faker $faker) {
    return [
        'text' => $faker->text(),
        'user_id' => factory(\Coyote\User::class)
    ];
});

$factory->state(\Coyote\Microblog::class, 'id', function (Faker $faker) {
    return [
        'id' => $faker->numberBetween(10000000)
    ];
});
