<?php
namespace Database\Seeders;

use Coyote\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'     => 'admin',
            'email'    => 'admin@localhost',
            'password' => bcrypt('admin'),
        ]);

        User::create([
            'name'     => 'user',
            'email'    => 'user@localhost',
            'password' => bcrypt('user'),
        ]);

        \factory(User::class, 10)->create();
    }
}
