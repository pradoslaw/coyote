<?php
namespace Database\Seeders;

use Coyote\Microblog;
use Coyote\User;
use Illuminate\Database\Seeder;

class MicroblogsTableSeeder extends Seeder
{
    use \SchemaBuilder;

    public function run(): void
    {
        $user = User::query()->inRandomOrder()->firstOrFail();
        $this->microblog($user, 'Aut omnis maiores minima');
        $this->microblog($user, 'eos qui reiciendis neque');
        $this->microblog($user, 'Omnis fugit odit sed dolorem.');
    }

    private function microblog(User $user, string $text): void
    {
        Microblog::query()->forceCreate([
            'user_id'      => $user->id,
            'text'         => $text,
            'is_sponsored' => true,
        ]);
    }
}
