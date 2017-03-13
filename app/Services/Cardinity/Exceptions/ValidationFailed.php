<?php

namespace Coyote\Services\Cardinity\Exceptions;

class ValidationFailed extends CardinityException
{
    protected $code = 400;
    protected $message = 'Bad Request – Your request contains field or business logic validation errors or provided JSON is malformed.';
}
