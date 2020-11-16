<?php

use Faker\Generator as Faker;

$factory->define(\Coyote\Job::class, function (Faker $faker) {
    return [
        'title' => $faker->realText(60),
        'description' => $faker->text(2000),
        'is_publish' => false,
        'boost_at' => now(),
        'user_id' => factory(\Coyote\User::class),
        'plan_id' => \Coyote\Plan::inRandomOrder()->get()->first()->id
    ];
});

$factory->afterCreating(\Coyote\Job::class, function (\Coyote\Job $job, $faker) {
    $job->payments()->save(factory(\Coyote\Payment::class)->make([
        'plan_id' => $job->plan_id
    ]));
});
