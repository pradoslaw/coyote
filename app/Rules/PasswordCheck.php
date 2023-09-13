<?php

namespace Coyote\Rules;

use Illuminate\Contracts\Auth;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Contracts\Validation\Rule;

class PasswordCheck implements Rule
{
    public function __construct(private Hasher $hasher, private Auth\Factory $auth)
    {
    }

    public function passes($attribute, $value): bool
    {
        return $this->hasher->check($value, $this->auth->guard()->user()->password);
    }

    public function message(): string
    {
        return 'Wprowadzone hasło nie jest poprawne.';
    }
}
