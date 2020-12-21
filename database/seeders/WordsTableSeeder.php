<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class WordsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Coyote\Word::create(['word' => 'chuj*', 'replacement' => 'ch**']);
        \Coyote\Word::create(['word' => 'kurwa', 'replacement' => 'ku***']);
    }
}
