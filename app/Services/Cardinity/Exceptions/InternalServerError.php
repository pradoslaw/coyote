<?php

namespace Coyote\Services\Cardinity\Exceptions;

class InternalServerError extends CardinityException
{
    protected $code = 500;
    protected $message = 'Internal Server Error – We had a problem on our end. Try again later.';
}
