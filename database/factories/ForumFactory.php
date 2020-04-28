<?php

use Faker\Generator as Faker;

$factory->define(\Coyote\Forum::class, function (Faker $faker) {
    $name = $faker->words(3, true);

    return [
        'name' => $name,
        'slug' => str_slug($name, '_'),
        'description' => $faker->text(40)
    ];
});
