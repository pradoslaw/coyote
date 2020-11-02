<?php

use Faker\Generator as Faker;

$factory->define(\Coyote\Job::class, function (Faker $faker) {
    return [
        'title' => $faker->jobTitle,
        'description' => $faker->text(2000),
        'deadline_at' => \Carbon\Carbon::now()->addDays(40),
        'is_publish' => true,
        'boost_at' => \Carbon\Carbon::now(),
        'user_id' => factory(\Coyote\User::class),
        'plan_id' => \Coyote\Plan::inRandomOrder()->first()->id
    ];
});

$factory->afterCreating(\Coyote\Job::class, function (\Coyote\Job $job, $faker) {
    $job->payments()->save(factory(\Coyote\Payment::class)->make([
        'plan_id' => $job->plan_id
    ]));
});
