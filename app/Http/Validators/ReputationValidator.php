<?php

namespace Coyote;

/**
 * Class ReputationValidator
 */
class ReputationValidator
{
    public function validateReputation($attribute, $value, $parameters, $validator)
    {
        return auth()->user()->reputation >= $parameters[0];
    }
}
