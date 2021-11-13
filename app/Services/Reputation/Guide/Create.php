<?php

namespace Coyote\Services\Reputation\Guide;

use Coyote\Guide as Model;
use Coyote\Services\Reputation\Reputation;
use Coyote\Services\UrlBuilder;

class Create extends Reputation
{
    const ID = \Coyote\Reputation::GUIDE;

    /**
     * @param int $guideId
     * @return $this
     */
    public function setGuideId(int $guideId): static
    {
        $this->metadata['guide_id'] = $guideId;

        return $this;
    }

    /**
     * @param Model $guide
     * @return $this
     */
    public function map(Model $guide): static
    {
        $this->setGuideId($guide->id);
        $this->setUrl(UrlBuilder::guide($guide));
        $this->setUserId($guide->user_id);
        $this->setExcerpt(excerpt($guide->title));

        return $this;
    }
}
