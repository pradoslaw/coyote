<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Coyote\Pm;
use Faker\Generator as Faker;

$factory->define(Pm::class, function (Faker $faker) {
    $text = Pm\Text::create(['text' => $faker->realText()]);

    return [
        'text_id' => $text->id,
        'user_id' => factory(\Coyote\User::class)->make(),
        'author_id' => factory(\Coyote\User::class)->make(),
        'folder' => Pm::SENTBOX
    ];
});

$factory->afterCreating(Pm::class, function ($pm, $faker) {
    Pm::create(['text_id' => $pm->text_id, 'user_id' => $pm->author_id, 'author_id' => $pm->user_id, 'folder' => Pm::INBOX]);
});

$factory->state(Pm::class, 'id', function (Faker $faker) {
    return [
        'id' => $faker->numberBetween(10000000)
    ];
});
