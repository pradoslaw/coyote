<?php

namespace Database\Seeders;

use Coyote\User;
use Illuminate\Database\Seeder;

class GuidesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Coyote\Guide::forceCreate([
            'title' => 'Jaki będzie wynik wykonanai poniższego kodu?',
            'excerpt' => '`true === 1`',
            'text' => 'Lorem ipsum lores',
            'user_id' => User::first()->id
        ]);
    }
}
