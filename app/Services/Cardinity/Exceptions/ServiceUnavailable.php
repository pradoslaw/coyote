<?php

namespace Coyote\Services\Cardinity\Exceptions;

class ServiceUnavailable extends CardinityException
{
    protected $code = 503;
    protected $message = 'Service Unavailable – We’re temporarily off-line for maintenance. Please try again later.';
}
