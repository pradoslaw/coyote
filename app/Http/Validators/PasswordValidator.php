<?php
namespace Coyote\Http\Validators;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Hashing\Hasher;

class PasswordValidator
{
    /**
     * @var Hasher
     */
    protected $hasher;

    /**
     * @var Guard
     */
    protected $auth;

    /**
     * @param Hasher $hasher
     * @param Guard $auth
     */
    public function __construct(Hasher $hasher, Guard $auth)
    {
        $this->hasher = $hasher;
        $this->auth = $auth;
    }

    /**
     * @param mixed $attribute
     * @param mixed $value
     * @param array $parameters
     * @param \Illuminate\Validation\Validator $validator
     * @return bool
     */
    public function validatePassword($attribute, $value, $parameters, $validator)
    {
        return $this->hasher->check($value, $this->auth->user()->getAuthPassword());
    }
}
