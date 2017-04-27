<?php

namespace Coyote\Services\Cardinity\Exceptions;

class Forbidden extends CardinityException
{
    protected $code = 403;
    protected $message = 'Forbidden – You do not have access to this resource.';
}
