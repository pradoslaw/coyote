<?php

namespace Coyote\Services\Cardinity\Exceptions;

class Declined extends CardinityException
{
    protected $code = 402;
    protected $message = 'Request Failed – Your request was valid but it was declined.';
}
