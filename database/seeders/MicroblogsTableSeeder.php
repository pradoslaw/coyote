<?php
namespace Database\Seeders;

use Coyote\Microblog;
use Coyote\User;
use Illuminate\Database\Seeder;

class MicroblogsTableSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->inRandomOrder()->firstOrFail();
        $this->microblog($user, 'Aut omnis maiores minima, https://4programmers.net');
        $this->microblog($user, 'eos qui reiciendis neque, https://4programmers.net');
        $lastId = $this->microblog($user, 'Omnis fugit odit sed dolorem, https://4programmers.net.');
        $this->microblog($user, 'Omnis fugit odit sed dolorem, https://4programmers.net.', $lastId);
    }

    private function microblog(User $user, string $text, ?int $parentId = null): int
    {
        $microblog = Microblog::query()->forceCreate([
            'user_id'      => $user->id,
            'text'         => $text,
            'is_sponsored' => true,
            'parent_id'    => $parentId,
        ]);
        return $microblog->id;
    }
}
