<?php

namespace Coyote\Rules;

use Ddeboer\Vatin\Validator;
use Illuminate\Contracts\Validation\Rule;

class VatIdRule implements Rule
{
    public function __construct(private string $countryCode)
    {
    }

    public function passes($attribute, $vatId): bool
    {
        // can't validate without country code
        if (!$this->countryCode) {
            return true;
        }

        if ($this->countryCode !== 'CH') {
            $vatId = preg_replace('/[^0-9A-Za-z]/', '', $vatId);
        }

        return (new Validator)->isValid($this->countryCode . $vatId);
    }

    public function message()
    {
        return trans('validation.invalid_vat_id');
    }
}
