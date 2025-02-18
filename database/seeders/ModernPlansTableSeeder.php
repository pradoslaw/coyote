<?php
namespace Database\Seeders;

use Coyote\Plan;
use Illuminate\Database\Seeder;

class ModernPlansTableSeeder extends Seeder
{
    public function run(): void
    {
        $this->createPlan('Free', price:0, length:14, boost:false);
        $this->createPlan('Premium', price:159);
        $this->createPlan('Strategic', price:357);
        $this->createPlan('Growth', price:495);
        $this->createPlan('Scale', price:1580);
    }

    private function createPlan(string $planName, int $price, ?int $length = null, bool $boost = true): void
    {
        Plan::query()->forceCreate([
            'name'      => $planName,
            'price'     => $price,
            'length'    => $length ?? 30,
            'vat_rate'  => 1.23,
            'discount'  => 0,
            'boost'     => $boost ? 3 : 0,
            'benefits'  => $boost
                ? ['is_publish', 'is_boost', 'is_ads']
                : ['is_publish'],
            'is_active' => true,
        ]);
    }
}
