<?php

use Faker\Generator as Faker;

$factory->define(\Coyote\Microblog::class, function (Faker $faker) {
    return [
        'text' => $faker->text(),
        'user_id' => function () {
            return factory(\Coyote\User::class)->create()->id;
        }
    ];
});
