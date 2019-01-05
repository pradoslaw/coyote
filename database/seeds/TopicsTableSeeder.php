<?php

use Illuminate\Database\Seeder;

class TopicsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\Coyote\Topic::class, 50)->create()->each(function (\Coyote\Topic $topic) {
            for ($i = 1; $i <= rand(1, 10); $i++) {
                $user = \Coyote\User::inRandomOrder()->first();

                $post = $topic->posts()->save(factory(\Coyote\Post::class)->make(['forum_id' => $topic->forum_id, 'user_id' => $user->id]));
                $post->logs()->create([
                    'user_id' => $user->id,
                    'subject' => $topic->subject,
                    'text' => $post->text,
                    'ip' => $post->ip,
                    'host' => $post->host,
                    'browser' => $post->browser
                ]);
            }
        });
    }
}
