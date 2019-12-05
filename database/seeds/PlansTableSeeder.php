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
        \Coyote\Plan::forceCreate([
            'name' => 'Standard',
            'price' => 30,
            'vat_rate' => 1,
            'discount' => 0,
            'length' => 40,
            'benefits' => ['is_publish', 'is_social']
        ]);

        \Coyote\Plan::forceCreate([
            'name' => 'Plus',
            'price' => 57,
            'vat_rate' => 1,
            'discount' => 0,
            'length' => 40,
            'benefits' => ['is_publish', 'is_social', 'is_boost', 'is_ads'],
            'is_default' => 1,
            'boost' => 1
        ]);

        \Coyote\Plan::forceCreate([
            'name' => 'Premium',
            'price' => 135,
            'vat_rate' => 1,
            'discount' => 0,
            'length' => 40,
            'benefits' => ['is_publish', 'is_social', 'is_boost', 'is_ads', 'is_highlight', 'is_on_top'],
            'boost' => 3
        ]);
    }
}
