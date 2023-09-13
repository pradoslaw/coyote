<?php

namespace Coyote\Rules;

use Ddeboer\Vatin\Validator;
use Illuminate\Contracts\Validation\Rule;

class VatIdRule implements Rule
{
    public function __construct(private string $countryCode)
    {
    }

    public function passes($attribute, $value): bool
    {
        if (!$this->countryCode) {
            return true;
        }
        if ($this->countryCode !== 'CH') {
            $value = \preg_replace('/[^0-9A-Za-z]/', '', $value);
        }
        return (new Validator)->isValid($this->countryCode . $value);
    }

    public function message(): string
    {
        return \trans('validation.invalid_vat_id');
    }
}
