<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Coyote\Forum\Reason;
use Coyote\Post;
use Coyote\Topic;
use Coyote\User;
use Illuminate\Database\Seeder;

class TrollUserSeeder extends Seeder
{
    public function run(): void
    {
        $reasons = [
            ...Reason::all()->map(fn(Reason $reason) => $reason->name)->all(),
            null,
        ];
        $topic = \factory(Topic::class)->create();
        $user = $this->oldUser();
        $this->seedPosts($user, $topic, $reasons);
    }

    private function seedPosts(User $user, Topic $topic, array $reasons): void
    {
        foreach (range(1, 100) as $_) {
            $this->seedPost($user, $topic, $reasons);
        }
    }

    private function seedPost(User $user, Topic $topic, array $reasons): void
    {
        $carbon = Carbon::today()->subHours(\rand(0, 365 * 24 * 24));
        $post = \factory(Post::class)->make([
            'forum_id'      => $topic->forum_id,
            'user_id'       => $user->id,
            'created_at'    => $carbon,
            'deleted_at'    => $carbon,
            'delete_reason' => $reasons[\array_rand($reasons)],
        ]);
        $topic->posts()->save($post);
    }

    private function oldUser(): User
    {
        /** @var User $oldUser */
        $oldUser = User::query()->firstOrCreate(['name' => 'trolluser'], [
            'email'      => 'trolluser@localhost',
            'password'   => bcrypt('trolluser'),
            'reputation' => 15000,
        ]);
        return $oldUser;
    }
}
