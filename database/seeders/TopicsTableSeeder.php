<?php

namespace Database\Seeders;

use Coyote\Post;
use Coyote\Post\Log;
use Coyote\Topic;
use Coyote\User;
use Illuminate\Database\Seeder;

class TopicsTableSeeder extends Seeder
{
    public function run(): void
    {
        Log::reguard();

        \factory(Topic::class, 30)->create()->each(function (Topic $topic) {
            for ($i = 1; $i <= rand(1, 10); $i++) {
                $user = User::inRandomOrder()->first();
                $post = \factory(Post::class)
                    ->make(['forum_id' => $topic->forum_id, 'user_id' => $user->id]);
                $topic->posts()->save($post);
            }
        });
    }
}
