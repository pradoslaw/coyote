<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Coyote\User::create([
            'name'                  => 'admin',
            'email'                 => 'admin@4programmers.net',
            'password'              => bcrypt('123')
        ]);

        factory(\Coyote\User::class, 50)->create();
    }
}
