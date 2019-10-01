<?php

use Faker\Generator as Faker;

$factory->define(\Coyote\Firm::class, function (Faker $faker) {
    return [
        'name' => $faker->company
    ];
});
