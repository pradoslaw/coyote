<?php

use Faker\Generator as Faker;

$factory->define(\Coyote\Group::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
    ];
});
