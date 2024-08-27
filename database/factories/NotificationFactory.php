<?php

use Coyote\Notification;
use Coyote\User;
use Faker\Generator as Faker;

$factory->define(Notification::class, function (Faker $faker): array {
    return [
        'id'         => $faker->uuid,
        'type_id'    => Notification\Type::query()->firstOrFail()->id,
        'user_id'    => factory(User::class),
        'created_at' => now(),
        'subject'    => $faker->text(50),
        'excerpt'    => $faker->text(200),
        'url'        => $faker->url,
        'object_id'  => $faker->randomDigit,
    ];
});

$factory->state(Notification::class, 'headline', [
    'headline' => '{sender} dodał odpowiedź w wątku',
]);
