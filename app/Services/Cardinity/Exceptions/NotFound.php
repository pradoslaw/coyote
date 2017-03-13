<?php

namespace Coyote\Services\Cardinity\Exceptions;

class NotFound extends CardinityException
{
    protected $code = 404;
    protected $message = 'Not Found – The specified resource could not be found.';
}
