<?php

namespace Coyote\Services\Cardinity\Exceptions;

use Exception;

class CardinityException extends \RuntimeException
{
    /**
     * @param Exception|null $previous
     */
    public function __construct(Exception $previous = null)
    {
        parent::__construct($this->message, $this->code, $previous);
    }
}
