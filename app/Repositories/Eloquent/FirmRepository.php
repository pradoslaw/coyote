<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\FirmRepositoryInterface;
use Coyote\Firm;

class FirmRepository extends Repository implements FirmRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return Firm::class;
    }

    /**
     * @inheritdoc
     */
    public function loadDefaultFirm($userId)
    {
        $firm = $this->findBy('user_id', $userId);

        if (!$firm) {
            /** @var \Coyote\Firm $firm */
            $firm = $this->newInstance();
            $firm->setDefaultUserId($userId);
        }

        return $firm;
    }
}
