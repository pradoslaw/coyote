<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PastebinSeeder extends Seeder
{
    public function run(): void
    {
        \Coyote\Pastebin::create([
            'text'  => 'example code',
            'title' => 'title',
        ]);
    }
}
