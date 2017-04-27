<?php

namespace Coyote\Services\Cardinity\Exceptions;

class NotAcceptable extends CardinityException
{
    protected $code = 406;
    protected $message = 'Not Acceptable – Wrong Accept headers sent in the request.';
}
