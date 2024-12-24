<?php

use Coyote\Forum;
use Coyote\Topic;
use Faker\Generator as Faker;

$factory->define(Topic::class, function (Faker $faker) {
    $title = $faker->text(60);
    return [
        'title'                => $title,
        'slug'                 => str_slug($title, '_'),
        'created_at'           => now(),
        'updated_at'           => now(),
        'last_post_created_at' => now(),
        'replies'              => 0,
        'replies_real'         => 0,
        'forum_id'             => fn() => Forum::query()->inRandomOrder()->firstOrFail()->id,
    ];
});

$factory->afterCreating(Topic::class, function (Topic $topic): void {
    $topic->posts()->saveMany($topic->posts);
});

$factory->afterMaking(Topic::class, function (Topic $topic) {
    $post = factory(\Coyote\Post::class)->make(['forum_id' => $topic->forum_id]);
    $post->setRelation('topic', $topic);
    $topic->setRelations([
        'posts'     => [$post],
        'firstPost' => $post,
        'lastPost'  => $post,
    ]);
});

$factory->state(Topic::class, 'tree', fn() => ['is_tree' => true]);

$factory->state(Topic::class, 'id', function (Faker $faker) {
    return ['id' => $faker->numberBetween(10000000)];
});

$factory->afterMakingState(Topic::class, 'id', function (Topic $topic, $faker) {
    $topic->firstPost->id = $faker->numberBetween(10000000);
});
