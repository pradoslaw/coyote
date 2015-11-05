<?php

use Illuminate\Database\Seeder;

class GroupTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $group = \Coyote\Group::create([
           'name'           => 'Administrator'
        ]);

        $user = DB::table('users')->orderBy('id')->first();

        \Coyote\User\Group::create([
           'group_id'       => $group->id,
           'user_id'        => $user->id
        ]);
    }
}
