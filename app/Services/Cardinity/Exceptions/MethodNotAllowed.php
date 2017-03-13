<?php

namespace Coyote\Services\Cardinity\Exceptions;

class MethodNotAllowed extends CardinityException
{
    protected $code = 405;
    protected $message = 'Method Not Allowed – You tried to access a resource using an invalid HTTP method.';
}
