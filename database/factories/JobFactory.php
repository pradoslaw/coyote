<?php

use Faker\Generator as Faker;

$factory->define(\Coyote\Job::class, function (Faker $faker) {
    return [
        'title' => $faker->jobTitle,
        'description' => $faker->text(2000),
        'deadline_at' => \Carbon\Carbon::now()->addDay(40)
    ];
});
