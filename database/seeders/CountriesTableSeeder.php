<?php
namespace Database\Seeders;

use Coyote\Country;
use Illuminate\Database\Seeder;

class CountriesTableSeeder extends Seeder
{
    public function run(): void
    {
        $this->createCountry('AT', 'Austria', 1);
        $this->createCountry('BE', 'Belgia', 1);
        $this->createCountry('CZ', 'Czechy', 1);
        $this->createCountry('DK', 'Dania', 1);
        $this->createCountry('FR', 'Francja', 1);
        $this->createCountry('ES', 'Hiszpania', 1);
        $this->createCountry('IE', 'Irlandia', 1);
        $this->createCountry('IS', 'Islandia', 1);
        $this->createCountry('LI', 'Liechtenstein', 1);
        $this->createCountry('LU', 'Luksemburg', 1);
        $this->createCountry('MT', 'Malta', 1);
        $this->createCountry('DE', 'Niemcy', 1);
        $this->createCountry('NO', 'Norwegia', 1);
        $this->createCountry('PL', 'Polska', 1.23);
        $this->createCountry('FI', 'Finlandia', 1);
        $this->createCountry('PT', 'Portugalia', 1);
        $this->createCountry('CH', 'Szwajcaria', 1);
        $this->createCountry('SE', 'Szwecja', 1);
        $this->createCountry('GB', 'Wielka Brytania', 1);
        $this->createCountry('US', 'USA', 1);
        $this->createCountry('IT', 'Włochy', 1);
        $this->createCountry('EN', 'Anglia', 1); // <-- wielka brytania powinna wystarczyc
        $this->createCountry('NL', 'Holandia', 1);
        $this->createCountry('SG', 'Singapur', 1);
    }

    private function createCountry(string $countryCode, string $name, float|int $vatRate): void
    {
        Country::query()->forceCreate([
            'name'     => $name,
            'code'     => $countryCode,
            'vat_rate' => $vatRate,
        ]);
    }
}
