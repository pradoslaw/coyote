<?php

namespace Coyote;

/**
 * Walidator sprawdza poprawnosc nazwy uzytkownika pod katem uzytych znakow. Nazwa uzytkownika
 * moze zawierac jedynie okreslony zbior znakow
 *
 * Class UsernameValidator
 */
class UsernameValidator
{
    public function validateUsername($attribute, $value, $parameters, $validator)
    {
        return preg_match('/^[0-9a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ._ -]+$/', $value);
    }
}
