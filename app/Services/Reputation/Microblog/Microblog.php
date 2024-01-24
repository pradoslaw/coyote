<?php
namespace Coyote\Services\Reputation\Microblog;

use Coyote\Microblog as Model;
use Coyote\Services\Reputation\Reputation;
use Coyote\Services\UrlBuilder;

abstract class Microblog extends Reputation
{
    /**
     * @param int $microblogId
     * @return $this
     */
    public function setMicroblogId($microblogId)
    {
        $this->metadata['microblog_id'] = $microblogId;
        return $this;
    }

    /**
     * @param Model $microblog
     * @return $this
     */
    public function map(Model $microblog)
    {
        $this->setMicroblogId($microblog->id);
        $this->setUrl(UrlBuilder::microblog($microblog));
        $this->setUserId($microblog->user_id);
        $this->setExcerpt(excerpt($microblog->html));

        return $this;
    }
}
