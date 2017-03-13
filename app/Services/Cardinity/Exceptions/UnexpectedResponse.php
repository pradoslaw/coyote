<?php

namespace Coyote\Services\Cardinity\Exceptions;

class UnexpectedResponse extends CardinityException
{
    protected $code = 0;
    protected $message = '';
}
