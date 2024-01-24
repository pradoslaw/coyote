<?php
namespace Coyote\Http\Validators;

use Illuminate\Contracts\Auth\Guard;

class ReputationValidator
{
    /**
     * @var Guard
     */
    protected $auth;

    /**
     * @param Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @param mixed $attribute
     * @param mixed $value
     * @param array $parameters
     * @param \Illuminate\Validation\Validator $validator
     * @return bool
     */
    public function validateReputation($attribute, $value, $parameters, $validator)
    {
        return $this->auth->user()->reputation >= $parameters[0];
    }
}
