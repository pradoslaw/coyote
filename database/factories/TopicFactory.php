<?php

use Faker\Generator as Faker;

$factory->define(\Coyote\Topic::class, function (Faker $faker) {
    $subject = $faker->text(60);

    return [
        'subject' => $subject,
        'slug' => str_slug($subject, '_'),
        'created_at' => now(),
        'updated_at' => now(),
        'last_post_created_at' => now(),
        'forum_id' => function () {
            return \Coyote\Forum::inRandomOrder()->first()->id;
        }
    ];
});

$factory->afterCreating(\Coyote\Topic::class, function (\Coyote\Topic $topic) {
    $topic->posts()->save(factory(\Coyote\Post::class)->make(['forum_id' => $topic->forum_id]));
});

$factory->state(\Coyote\Topic::class, 'id', function ($faker) {
    return [
        'id' => $faker->randomDigit,
    ];
});


//
//$factory->afterMaking(\Coyote\Topic::class, function (\Coyote\Topic $topic, Faker $faker) {
//    $topic->id = $faker->randomDigit;
//});
