<?php

namespace Coyote\Services\Cardinity;

class Payment
{
    const PENDING = 'pending';
    const APPROVED = 'approved';
    const DECLINED = 'declined';

    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $status;

    /**
     * @var AuthorizationInformation
     */
    public $authorizationInformation;

    /**
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === self::PENDING;
    }
}
