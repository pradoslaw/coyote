<?php

namespace Coyote\Services\Cardinity\Payment;

use Coyote\Services\Cardinity\MethodInterface;
use Coyote\Services\Cardinity\Payment;

class Create implements MethodInterface
{
    const CARD = 'card';
    const RECURRING = 'recurring';

    /**
     * @var array
     */
    private $attributes;

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return 'payments';
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return new Payment();
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return MethodInterface::POST;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
