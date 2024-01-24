<?php
namespace Coyote\Http\Validators;

use Illuminate\Validation\Validator;

class EmailValidator extends UserValidator
{
    /**
     * Check if email is already taken by another user (case insensitive)
     *
     * @param array $attribute
     * @param string $value
     * @param array $parameters
     * @return bool TRUE if user email is not taken (FALSE otherwise)
     */
    public function validateUnique($attribute, $value, $parameters)
    {
        return $this->validateBy('email', $value, (int)($parameters[0] ?? null));
    }

    /**
     * Return TRUE if email exists and is confirmed.
     *
     * @param array $attribute
     * @param string $value
     * @param array $parameters
     * @param Validator $validator
     * @return bool
     */
    public function validateConfirmed($attribute, $value, $parameters, $validator)
    {
        if ($this->user->findByEmail(mb_strtolower($value)) !== null) {
            return true;
        }

        $validator->addReplacer('email_confirmed', function ($message) {
            return str_replace(':link', url('Confirm'), $message);
        });

        return false;
    }
}
