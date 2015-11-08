<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call(UserTableSeeder::class);
        $this->call(GroupTableSeeder::class);
        $this->call(AclPermissionTableSeeder::class);
        $this->call(WordTableSeeder::class);
        $this->call(TagTableSeeder::class);
    }
}
