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
        Country::forceCreate(['name' => 'Austria', 'code' => 'AT']);
        Country::forceCreate(['name' => 'Belgia', 'code' => 'BE']);
        Country::forceCreate(['name' => 'Czechy', 'code' => 'CZ']);
        Country::forceCreate(['name' => 'Dania', 'code' => 'DK']);
        Country::forceCreate(['name' => 'Francja', 'code' => 'FR']);
        Country::forceCreate(['name' => 'Hiszpania', 'code' => 'ES']);
        Country::forceCreate(['name' => 'Irlandia', 'code' => 'IE']);
        Country::forceCreate(['name' => 'Islandia', 'code' => 'IS']);
        Country::forceCreate(['name' => 'Liechtenstein', 'code' => 'LI', 'eu' => false]);
        Country::forceCreate(['name' => 'Luksemburg', 'code' => 'LU']);
        Country::forceCreate(['name' => 'Malta', 'code' => 'MT']);
        Country::forceCreate(['name' => 'Niemcy', 'code' => 'DE']);
        Country::forceCreate(['name' => 'Norwegia', 'code' => 'NO', 'eu' => false]);
        Country::forceCreate(['name' => 'Polska', 'code' => 'PL']);
        Country::forceCreate(['name' => 'Finlandia', 'code' => 'FI']);
        Country::forceCreate(['name' => 'Portugalia', 'code' => 'PT']);
        Country::forceCreate(['name' => 'Szwajcaria', 'code' => 'CH', 'eu' => false]);
        Country::forceCreate(['name' => 'Szwecja', 'code' => 'SE']);
        Country::forceCreate(['name' => 'Wielka Brytania', 'code' => 'GB']);
        Country::forceCreate(['name' => 'USA', 'code' => 'US', 'eu' => false]);
        Country::forceCreate(['name' => 'Włochy', 'code' => 'IT']);
        Country::forceCreate(['name' => 'Anglia', 'code' => 'EN']); // <-- wielka brytania powinna wystarczyc
        Country::forceCreate(['name' => 'Holandia', 'code' => 'NL']);
        Country::forceCreate(['name' => 'Singapur', 'code' => 'SG', 'eu' => false]);
    }
}
