<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Coyote\Page::create(['title' => 'Forum', 'path' => '/Forum']);
        \Coyote\Page::create(['title' => 'Mikroblogi', 'path' => '/Mikroblogi']);
        \Coyote\Page::create(['title' => 'Praca', 'path' => '/Praca']);
        \Coyote\Page::create(['title' => 'Pastebin', 'path' => '/Pastebin']);
    }
}
