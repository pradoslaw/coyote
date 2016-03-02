<?php

use Illuminate\Database\Seeder;
use \Coyote\Job\Rate;

class JobRatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Rate::create(['name' => 'miesięcznie']);
        Rate::create(['name' => 'rocznie']);
        Rate::create(['name' => 'tygodniowo']);
        Rate::create(['name' => 'godzinowo']);
    }
}
