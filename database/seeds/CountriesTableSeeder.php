<?php

use Illuminate\Database\Seeder;
use Coyote\Country;

class CountriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Country::create(['name' => 'Austria']);
        Country::create(['name' => 'Belgia']);
        Country::create(['name' => 'Czechy']);
        Country::create(['name' => 'Dania']);
        Country::create(['name' => 'Francja']);
        Country::create(['name' => 'Hiszpania']);
        Country::create(['name' => 'Irlandia']);
        Country::create(['name' => 'Islandia']);
        Country::create(['name' => 'Liechtenstein']);
        Country::create(['name' => 'Luksemburg']);
        Country::create(['name' => 'Malta']);
        Country::create(['name' => 'Niemcy']);
        Country::create(['name' => 'Norwegia']);
        Country::create(['name' => 'Polska']);
        Country::create(['name' => 'Finlandia']);
        Country::create(['name' => 'Portulgalia']);
        Country::create(['name' => 'Szwajcaria']);
        Country::create(['name' => 'Szwecja']);
        Country::create(['name' => 'Wielka Brytania']);
        Country::create(['name' => 'USA']);
        Country::create(['name' => 'Włochy']);
        Country::create(['name' => 'Anglia']);
        Country::create(['name' => 'Holandia']);
        Country::create(['name' => 'Singapur']);
    }
}
