<?php
namespace Coyote\Services\Invoice;

use Coyote\Country;

readonly class VatRateCalculator
{
    private float $defaultVatRate;

    public function __construct()
    {
        $this->defaultVatRate = config('vendor.default_vat_rate');
    }

    public function vatRate(?Country $country, ?string $vatId): float
    {
        if ($vatId && $country) {
            return $country->vat_rate ?? $this->defaultVatRate;
        }
        return $this->defaultVatRate;
    }
}
