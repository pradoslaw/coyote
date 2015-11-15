<?php

use Illuminate\Database\Seeder;

class MicroblogTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = DB::table('users')->orderBy('id')->first();

        \Coyote\Microblog::create([
            'user_id'           => $user->id,
            'text'              => 'Lorem ipsum lores'
        ]);
    }
}
