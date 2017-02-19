<?php

use Illuminate\Database\Seeder;

class PackagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Coyote\Package::forceCreate(['name' => 'Ogłoszenie na górze listy', 'price' => 9]);
    }
}
