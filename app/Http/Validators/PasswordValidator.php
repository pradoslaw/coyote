<?php

namespace Coyote;

use Illuminate\Support\Facades\Hash;

/**
 * Walidator sprawdza poprawnosc obecnego hasla (porownuje hash w bazie danych)
 *
 * Class PasswordValidator
 */
class PasswordValidator
{
    public function validatePassword($attribute, $value, $parameters, $validator)
    {
        return Hash::check($value, auth()->user()->getAuthPassword());
    }
}
