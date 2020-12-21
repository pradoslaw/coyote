<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class GroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $group = new \Coyote\Group;
        $group->fill([
           'name'           => 'Administrator'
        ]);

        $group->system = true;
        $group->save();

        $user = DB::table('users')->orderBy('id')->first();

        \Coyote\Group\User::create([
           'group_id'       => $group->id,
           'user_id'        => $user->id
        ]);
    }
}
