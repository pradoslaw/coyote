<?php

namespace Coyote\Http\Validators;

/**
 * Class EmailValidator
 */
class EmailValidator extends UserValidator
{
    /**
     * Check if email is already taken by another user (case insensitive)
     *
     * @param $attribute
     * @param $value
     * @param $parameters
     * @return bool TRUE if user email is not taken (FALSE otherwise)
     */
    public function validateUnique($attribute, $value, $parameters)
    {
        return $this->validateBy('email', $value, (int) ($parameters[0] ?? null));
    }
}
