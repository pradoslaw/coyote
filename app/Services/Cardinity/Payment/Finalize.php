<?php

namespace Coyote\Services\Cardinity\Payment;

use Coyote\Services\Cardinity\MethodInterface;
use Coyote\Services\Cardinity\Payment;

class Finalize implements MethodInterface
{
    /**
     * @var string
     */
    private $paymentId;

    /**
     * @var string
     */
    private $authorizeData;

    /**
     * @param string $paymentId
     * @param string $authorizeData
     */
    public function __construct(string $paymentId, string $authorizeData)
    {
        $this->paymentId = $paymentId;
        $this->authorizeData = $authorizeData;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return sprintf('payments/%s', $this->paymentId);
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return MethodInterface::PATCH;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return new Payment();
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return [
            'authorize_data' => $this->authorizeData
        ];
    }
}
