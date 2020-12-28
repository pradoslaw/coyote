<?php

use Faker\Generator as Faker;

$factory->define(\Coyote\Topic::class, function (Faker $faker) {
    $title = $faker->text(60);

    return [
        'title' => $title,
        'slug' => str_slug($title, '_'),
        'created_at' => now(),
        'updated_at' => now(),
        'last_post_created_at' => now(),
        'replies' => 0,
        'replies_real' => 0,
        'forum_id' => function () {
            return \Coyote\Forum::inRandomOrder()->first()->id;
        }
    ];
});

$factory->afterCreating(\Coyote\Topic::class, function (\Coyote\Topic $topic) {
    $topic->posts()->saveMany($topic->posts);
});

$factory->afterMaking(\Coyote\Topic::class, function (\Coyote\Topic $topic) {
    $post = factory(\Coyote\Post::class)->make(['forum_id' => $topic->forum_id]);

    $post->setRelation('topic', $topic);

    $topic->setRelations([
        'posts' => [$post],
        'firstPost' => $post,
        'lastPost' => $post
    ]);
});

$factory->state(\Coyote\Topic::class, 'id', function (Faker $faker) {
    return [
        'id' => $faker->numberBetween(10000000)
    ];
});

$factory->afterMakingState(\Coyote\Topic::class, 'id', function (\Coyote\Topic $topic, $faker) {
    $topic->firstPost->id = $faker->numberBetween(10000000);
});
