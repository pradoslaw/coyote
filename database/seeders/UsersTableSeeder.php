<?php
namespace Database\Seeders;

use Coyote\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->forceCreate([
            'name'       => 'user',
            'email'      => 'user@localhost',
            'password'   => bcrypt('user'),
            'reputation' => 100,
            'is_confirm' => true,
        ]);
        \factory(User::class, 10)->create();
    }
}
