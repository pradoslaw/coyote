<?php

use Faker\Generator as Faker;

$factory->define(\Coyote\Forum::class, function (Faker $faker) {
    $name = $faker->text(10);

    return [
        'name' => $name,
        'slug' => str_slug($name, '_'),
        'description' => $faker->text(40)
    ];
});
