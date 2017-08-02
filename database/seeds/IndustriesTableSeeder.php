<?php

use Illuminate\Database\Seeder;

class IndustriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Coyote\Industry::forceCreate(['name' => 'B2B']);
        \Coyote\Industry::forceCreate(['name' => 'B2C']);
        \Coyote\Industry::forceCreate(['name' => 'Bankowość']);
        \Coyote\Industry::forceCreate(['name' => 'Big Data']);
        \Coyote\Industry::forceCreate(['name' => 'Reklama']);
        \Coyote\Industry::forceCreate(['name' => 'CMS']);
        \Coyote\Industry::forceCreate(['name' => 'Telekomunikacja']);
        \Coyote\Industry::forceCreate(['name' => 'Telefonia komórkowa']);
        \Coyote\Industry::forceCreate(['name' => 'Gry komputerowe']);
        \Coyote\Industry::forceCreate(['name' => 'Grafika komputerowa']);
        \Coyote\Industry::forceCreate(['name' => 'Konsulting']);
        \Coyote\Industry::forceCreate(['name' => 'CRM']);
        \Coyote\Industry::forceCreate(['name' => 'Bezpieczeństwo']);
        \Coyote\Industry::forceCreate(['name' => 'Bazy danych']);
        \Coyote\Industry::forceCreate(['name' => 'E-commerce']);
        \Coyote\Industry::forceCreate(['name' => 'Elektronika']);
        \Coyote\Industry::forceCreate(['name' => 'eLearning']);
        \Coyote\Industry::forceCreate(['name' => 'Marketing']);
        \Coyote\Industry::forceCreate(['name' => 'Rozrywka']);
        \Coyote\Industry::forceCreate(['name' => 'Software house']);
        \Coyote\Industry::forceCreate(['name' => 'Sport']);
        \Coyote\Industry::forceCreate(['name' => 'Moda']);
        \Coyote\Industry::forceCreate(['name' => 'Hazard']);
        \Coyote\Industry::forceCreate(['name' => 'Edukacja']);
        \Coyote\Industry::forceCreate(['name' => 'HR']);
        \Coyote\Industry::forceCreate(['name' => 'Ubezpieczenia']);
        \Coyote\Industry::forceCreate(['name' => 'IoT']);
        \Coyote\Industry::forceCreate(['name' => 'Prawo']);
        \Coyote\Industry::forceCreate(['name' => 'Służby bezpieczeństwa']);
        \Coyote\Industry::forceCreate(['name' => 'Wojskowość']);
        \Coyote\Industry::forceCreate(['name' => 'Machine Learning']);
        \Coyote\Industry::forceCreate(['name' => 'Medycyna']);
        \Coyote\Industry::forceCreate(['name' => 'Muzyka']);
        \Coyote\Industry::forceCreate(['name' => 'Dziennikarstwo']);
        \Coyote\Industry::forceCreate(['name' => 'Q&A']);
        \Coyote\Industry::forceCreate(['name' => 'Zarządzanie projektami']);
        \Coyote\Industry::forceCreate(['name' => 'Turystyka']);
        \Coyote\Industry::forceCreate(['name' => 'Gastronomia']);
        \Coyote\Industry::forceCreate(['name' => 'SaaS']);
        \Coyote\Industry::forceCreate(['name' => 'Nauka']);
        \Coyote\Industry::forceCreate(['name' => 'Media społecznościowe']);
        \Coyote\Industry::forceCreate(['name' => 'Rozpoznawanie mowy']);
        \Coyote\Industry::forceCreate(['name' => 'Telemetryka']);
        \Coyote\Industry::forceCreate(['name' => 'Transport']);
        \Coyote\Industry::forceCreate(['name' => 'Tłumaczenia']);
        \Coyote\Industry::forceCreate(['name' => 'VoIP']);
        \Coyote\Industry::forceCreate(['name' => 'Efekty specjalne']);
        \Coyote\Industry::forceCreate(['name' => 'Streaming wideo']);
        \Coyote\Industry::forceCreate(['name' => 'Sektor publiczny']);
    }
}
