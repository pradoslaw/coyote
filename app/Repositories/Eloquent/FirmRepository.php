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
    public function loadDefaultFirm(int $userId): Firm
    {
        $firm = $this->findBy('user_id', $userId);

        if (!$firm) {
            /** @var \Coyote\Firm $firm */
            $firm = $this->newInstance();
            $firm->setDefaultUserId($userId);
        }

        return $firm;
    }

    /**
     * @inheritDoc
     */
    public function loadFirm(int $userId, string $name): Firm
    {
        $firm = $this->where('user_id', $userId)->where('name', $name)->first();

        if (!$firm) {
            $firm = $this->newInstance();
        }

        return $firm;
    }
}
