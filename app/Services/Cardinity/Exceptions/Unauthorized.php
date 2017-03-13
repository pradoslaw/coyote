<?php

namespace Coyote\Services\Cardinity\Exceptions;

class Unauthorized
{
    protected $code = 401;
    protected $message = 'Unauthorized – Your authorization information was missing or wrong.';
}
