<?php

use Faker\Generator as Faker;

$factory->define(\Coyote\Job::class, function (Faker $faker) {
    $title = $faker->realText(60);

    return [
        'title' => $title,
        'description' => $faker->text(2000),
        'is_publish' => false,
        'boost_at' => now(),
        'user_id' => factory(\Coyote\User::class),
        'plan_id' => \Coyote\Plan::where('is_active', 1)->inRandomOrder()->get()->first()->id,
        'created_at' => now(),
        'updated_at' => now(),
        'deadline_at' => now()
    ];
});

$factory->afterCreating(\Coyote\Job::class, function (\Coyote\Job $job, $faker) {
    $job->payments()->save(factory(\Coyote\Payment::class)->make([
        'plan_id' => $job->plan_id,
        'status_id' => \Coyote\Payment::NEW
    ]));
});

$factory->afterCreatingState(\Coyote\Job::class, 'firm', function (\Coyote\Job $job, Faker $faker) {
    $job->firm()->associate(factory(\Coyote\Firm::class)->create(['user_id' => $job->user_id]));
});

$factory->state(\Coyote\Job::class, 'id', function (Faker $faker) {
    return [
        'id' => $faker->numberBetween(10000000)
    ];
});
