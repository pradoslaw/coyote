<?php

namespace Coyote\Services\Reputation\Microblog;

use Coyote\Services\Reputation\Reputation;
use Coyote\Services\Reputation\ReputationInterface;
use Coyote\Microblog as Model;

/**
 * Class Microblog
 */
abstract class Microblog extends Reputation implements ReputationInterface
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
        $this->setUrl(route('microblog.view', [$microblog->id], false));
        $this->setUserId($microblog->user_id);
        $this->setExcerpt(excerpt($microblog->text));

        return $this;
    }
}
