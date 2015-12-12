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
        $this->call(PermissionTableSeeder::class);
        $this->call(WordTableSeeder::class);
        $this->call(TagTableSeeder::class);
        $this->call(ReputationTypesTableSeeder::class);
        $this->call(MicroblogTableSeeder::class);
        $this->call(AlertTypeTableSeeder::class);
        $this->call(ForumTableSeeder::class);
    }
}
