<?php

namespace Coyote\Repositories\Contracts;

use Coyote\Firm;

interface FirmRepositoryInterface extends RepositoryInterface
{
    /**
     * Load user's default firm
     *
     * @param int $userId
     * @return Firm
     */
    public function loadDefaultFirm(int $userId): Firm;

    /**
     * @param int $userId
     * @param string $name
     * @return Firm
     */
    public function loadFirm(int $userId, string $name): Firm;
}
