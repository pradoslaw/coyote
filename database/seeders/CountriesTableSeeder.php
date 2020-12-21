<?php

namespace Database\Seeders;

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
        Country::forceCreate(['name' => 'Austria', 'code' => 'AT', 'vat_rate' => 1]);
        Country::forceCreate(['name' => 'Belgia', 'code' => 'BE', 'vat_rate' => 1]);
        Country::forceCreate(['name' => 'Czechy', 'code' => 'CZ', 'vat_rate' => 1]);
        Country::forceCreate(['name' => 'Dania', 'code' => 'DK', 'vat_rate' => 1]);
        Country::forceCreate(['name' => 'Francja', 'code' => 'FR', 'vat_rate' => 1]);
        Country::forceCreate(['name' => 'Hiszpania', 'code' => 'ES', 'vat_rate' => 1]);
        Country::forceCreate(['name' => 'Irlandia', 'code' => 'IE', 'vat_rate' => 1]);
        Country::forceCreate(['name' => 'Islandia', 'code' => 'IS', 'vat_rate' => 1]);
        Country::forceCreate(['name' => 'Liechtenstein', 'code' => 'LI', 'vat_rate' => 1]);
        Country::forceCreate(['name' => 'Luksemburg', 'code' => 'LU', 'vat_rate' => 1]);
        Country::forceCreate(['name' => 'Malta', 'code' => 'MT', 'vat_rate' => 1]);
        Country::forceCreate(['name' => 'Niemcy', 'code' => 'DE', 'vat_rate' => 1]);
        Country::forceCreate(['name' => 'Norwegia', 'code' => 'NO', 'vat_rate' => 1]);
        Country::forceCreate(['name' => 'Polska', 'code' => 'PL', 'vat_rate' => 1]);
        Country::forceCreate(['name' => 'Finlandia', 'code' => 'FI', 'vat_rate' => 1]);
        Country::forceCreate(['name' => 'Portugalia', 'code' => 'PT', 'vat_rate' => 1]);
        Country::forceCreate(['name' => 'Szwajcaria', 'code' => 'CH', 'vat_rate' => 1]);
        Country::forceCreate(['name' => 'Szwecja', 'code' => 'SE', 'vat_rate' => 1]);
        Country::forceCreate(['name' => 'Wielka Brytania', 'code' => 'GB', 'vat_rate' => 1]);
        Country::forceCreate(['name' => 'USA', 'code' => 'US', 'vat_rate' => 1]);
        Country::forceCreate(['name' => 'Włochy', 'code' => 'IT', 'vat_rate' => 1]);
        Country::forceCreate(['name' => 'Anglia', 'code' => 'EN', 'vat_rate' => 1]); // <-- wielka brytania powinna wystarczyc
        Country::forceCreate(['name' => 'Holandia', 'code' => 'NL', 'vat_rate' => 1]);
        Country::forceCreate(['name' => 'Singapur', 'code' => 'SG', 'vat_rate' => 1]);
    }
}
