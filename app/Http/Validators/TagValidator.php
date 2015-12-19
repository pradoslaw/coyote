<?php

namespace Coyote;

class TagValidator
{
    public function validateTag($attribute, $value, $parameters, $validator)
    {
        return preg_match('/[a-ząęśżźćółń0-9\-\.#,\+\ ]/', trim($value));
    }
}
