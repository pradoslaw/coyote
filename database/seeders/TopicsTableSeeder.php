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
        \factory(Topic::class, 30)
            ->create()
            ->each(fn(Topic $topic) => $this->seedPosts($topic));
    }

    private function seedPosts(Topic $topic): void
    {
        foreach ($this->randomRange(15) as $_) {
            $this->seedPost($topic, $this->randomUser());
        }
    }

    private function randomRange(int $length): array
    {
        return \range(0, \rand(0, $length - 1));
    }

    private function seedPost(Topic $topic, User $user): void
    {
        $post = \factory(Post::class)->make(['forum_id' => $topic->forum_id, 'user_id' => $user->id]);
        $topic->posts()->save($post);
    }

    private function randomUser(): User
    {
        /** @var User $user */
        $user = User::query()->inRandomOrder()->first();
        return $user;
    }
}
