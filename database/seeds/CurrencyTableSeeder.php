<?php

use Illuminate\Database\Seeder;
use Coyote\Currency;

class CurrencyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Currency::create(['name' => 'zł']);
        Currency::create(['name' => '€']);
        Currency::create(['name' => '$']);
        Currency::create(['name' => '£']);
        Currency::create(['name' => 'CHF']);
    }
}
