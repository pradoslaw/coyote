<?php

use Illuminate\Database\Seeder;

class PlansTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Coyote\Plan::forceCreate(['name' => 'Promocja ogłoszenia', 'price' => 9]);
    }
}
