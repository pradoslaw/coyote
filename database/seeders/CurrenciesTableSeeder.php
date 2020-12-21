<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Coyote\Currency;

class CurrenciesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Currency::forceCreate(['name' => 'PLN', 'symbol' => 'zł']);
        Currency::forceCreate(['name' => 'EUR', 'symbol' => '€']);
        Currency::forceCreate(['name' => 'USD', 'symbol' => '$']);
        Currency::forceCreate(['name' => 'GBP', 'symbol' => '£']);
        Currency::forceCreate(['name' => 'CHF', 'symbol' => '₣']);
    }
}
