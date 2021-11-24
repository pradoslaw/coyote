<?php

use Faker\Generator as Faker;

$factory->define(\Coyote\Payment::class, function (Faker $faker) {
    return [
        'id' => $faker->uuid,
        'status_id' => \Coyote\Payment::NEW,
        'days' => 40,
        'plan_id' => \Coyote\Plan::where('name', 'Plus')->first()
    ];
});
