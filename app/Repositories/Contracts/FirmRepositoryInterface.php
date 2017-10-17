<?php

namespace Coyote\Repositories\Contracts;

interface FirmRepositoryInterface extends RepositoryInterface
{
    /**
     * Load user's default firm
     *
     * @param int $userId
     * @return \Coyote\Firm
     */
    public function loadDefaultFirm($userId);
}
