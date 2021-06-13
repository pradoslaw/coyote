<?php

namespace Coyote\Repositories\Contracts;

use Coyote\Campaign;

interface CampaignRepositoryInterface extends RepositoryInterface
{
    /**
     * @return Campaign[]
     */
    public function campaigns();
}
