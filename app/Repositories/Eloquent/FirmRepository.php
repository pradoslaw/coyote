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
        $firm = $this
            ->model
            ->select('firms.*')
            ->where('firms.user_id', $userId)
            ->orderBy('firms.id', 'DESC')
            ->limit(1)
            ->first();

        return $firm ?? $this->newInstance();
    }

    /**
     * @inheritDoc
     */
    public function loadFirm(int $userId, string $name): Firm
    {
        return $this->where('user_id', $userId)->where('name', $name)->first() ?? $this->newInstance();
    }
}
