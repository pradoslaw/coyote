<?php

use Faker\Generator as Faker;

$factory->define(\Coyote\Notification::class, function (Faker $faker) {
    return [
        'type_id' => \Coyote\Notification\Type::first()->id,
        'user_id' => factory(\Coyote\User::class)->make(),
        'created_at' => now(),
        'subject' => $faker->text(50),
        'excerpt' => $faker->text(200),
        'url' => $faker->url,
        'object_id' => $faker->randomDigit,
        'headline' => '{sender} dodał odpowiedź w wątku'
    ];
});
