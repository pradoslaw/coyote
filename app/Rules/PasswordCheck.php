<?php

namespace Coyote\Rules;

use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Auth\Factory as Auth;

class PasswordCheck implements Rule
{
    /**
     * @var Hasher
     */
    private $hasher;

    /**
     * @var Auth
     */
    private $auth;

    /**
     * PasswordRule constructor.
     * @param Hasher $hasher
     * @param Auth $auth
     */
    public function __construct(Hasher $hasher, Auth $auth)
    {
        $this->hasher = $hasher;
        $this->auth = $auth;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $this->hasher->check($value, $this->auth->guard()->user()->password);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Wprowadzone hasło nie jest poprawne.';
    }
}
