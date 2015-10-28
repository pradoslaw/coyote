<?php

class UsernameValidator
{
    public function validateUsername($attribute, $value, $parameters, $validator)
    {
        return preg_match('/^[0-9a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ._ -]+$/', $value);
    }
}