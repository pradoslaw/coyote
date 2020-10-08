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
        \Coyote\Post\Log::reguard();

        factory(\Coyote\Topic::class, 50)->create()->each(function (\Coyote\Topic $topic) {
            for ($i = 1; $i <= rand(1, 10); $i++) {
                $user = \Coyote\User::inRandomOrder()->first();

                $topic->posts()->save(factory(\Coyote\Post::class)->make(['forum_id' => $topic->forum_id, 'user_id' => $user->id]));
            }
        });
    }
}
