<?php

namespace Coyote\Reputation\Microblog;

use Coyote\Microblog;
use Coyote\Reputation\Reputation;
use Coyote\Reputation\ReputationInterface;

/**
 * Class Create
 * @package Coyote\Reputation\Microblog
 */
class Create extends Reputation implements ReputationInterface
{
    const ID = \Coyote\Reputation::MICROBLOG;

    /**
     * Cofniecie pkt reputacji za dany wpis (np. przy usuwaniu wpisu)
     *
     * @param int $microblogId
     */
    public function undo($microblogId)
    {
        $result = $this->reputation
            ->where('type_id', '=', self::ID)
            ->whereRaw("metadata->>'microblog_id' = ?", [$microblogId])
            ->first();

        if ($result) {
            $this->setIsPositive(false);

            $this->save($result->toArray());
        }
    }

    public function map(Microblog $microblog)
    {
        $this->setMicroblogId($microblog->id);
        $this->setUrl(route('microblog.view', [$microblog->id], false));
        $this->setUserId($microblog->user_id);
        $this->setExcerpt(excerpt($microblog->text));

        return $this;
    }
}
