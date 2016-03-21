<?php

namespace Coyote;

class TagValidator
{
    const REGEXP = '[a-ząęśżźćółń0-9\-\.#,\+]';

    public function validateTag($attribute, $value, $parameters, $validator)
    {
        return preg_match('/' . self::REGEXP . '/', trim($value));
    }
}
