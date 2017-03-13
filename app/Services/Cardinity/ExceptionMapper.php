<?php

namespace Coyote\Services\Cardinity;

use Coyote\Services\Cardinity\Exceptions\CardinityException;
use Coyote\Services\Cardinity\Exceptions\Declined;
use Coyote\Services\Cardinity\Exceptions\Forbidden;
use Coyote\Services\Cardinity\Exceptions\InternalServerError;
use Coyote\Services\Cardinity\Exceptions\MethodNotAllowed;
use Coyote\Services\Cardinity\Exceptions\NotAcceptable;
use Coyote\Services\Cardinity\Exceptions\NotFound;
use Coyote\Services\Cardinity\Exceptions\ServiceUnavailable;
use Coyote\Services\Cardinity\Exceptions\Unauthorized;
use Coyote\Services\Cardinity\Exceptions\UnexpectedResponse;
use Coyote\Services\Cardinity\Exceptions\ValidationFailed;
use GuzzleHttp\Exception\ClientException;

class ExceptionMapper
{
    /**
     * @param ClientException $exception
     * @return CardinityException
     */
    public function dispatch(ClientException $exception): CardinityException
    {
        $map = $this->getMap();

        if (array_key_exists($exception->getCode(), $map)) {
            return $this->createException($map[$exception->getCode()], $exception);
        }

        return $this->createException(UnexpectedResponse::class, $exception);
    }

    /**
     * @return array
     */
    private function getMap()
    {
        return [
            400 => ValidationFailed::class,
            401 => Unauthorized::class,
            402 => Declined::class,
            403 => Forbidden::class,
            404 => NotFound::class,
            405 => MethodNotAllowed::class,
            406 => NotAcceptable::class,
            500 => InternalServerError::class,
            503 => ServiceUnavailable::class
        ];
    }

    /**
     * @param string $class
     * @param ClientException $exception
     * @return CardinityException
     */
    private function createException(string $class, ClientException $exception): CardinityException
    {
        return new $class($exception);
    }
}
