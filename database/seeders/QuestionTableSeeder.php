<?php

namespace Database\Seeders;

use Coyote\User;
use Illuminate\Database\Seeder;

class QuestionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Coyote\Models\Question::forceCreate([
            'title' => 'Jaki będzie wynik wykonanai poniższego kodu?',
            'question' => '`true === 1`',
            'answer' => 'Lorem ipsum lores',
            'user_id' => User::first()->id
        ]);
    }
}
