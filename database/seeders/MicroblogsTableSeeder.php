<?php
namespace Database\Seeders;

use Coyote\Microblog;
use Illuminate\Database\Seeder;

class MicroblogsTableSeeder extends Seeder
{
    use \SchemaBuilder;

    public function run(): void
    {
        $user = $this->db->table('users')->orderBy('id')->first();
        Microblog::query()->create([
            'user_id' => $user->id,
            'text'    => 'Lorem ipsum lores',
        ]);
    }
}
