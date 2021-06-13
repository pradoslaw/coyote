<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Campaign;
use Coyote\Repositories\Contracts\CampaignRepositoryInterface;

class CampaignRepository extends Repository implements CampaignRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return Campaign::class;
    }

    public function campaigns()
    {
        return $this
            ->model
            ->select()
            ->where('is_enabled', true)
            ->with('banners')
            ->get();
    }
}
