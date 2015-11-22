<?php

namespace Coyote\Reputation\Microblog;

use Coyote\Reputation\Reputation;
use Coyote\Reputation\ReputationInterface;

/**
 * Class Create
 * @package Coyote\Reputation\Microblog
 */
class Create extends Reputation implements ReputationInterface
{
    const ID = 3;

    /**
     * Cofniecie pkt reputacji za dany wpis (np. przy usuwaniu wpisu)
     *
     * @param int $microblogId
     */
    public function undo($microblogId)
    {
        $result = $this->reputation->whereRaw("metadata->>'microblog_id' = ?", [$microblogId])->first();
        if ($result) {
            $this->setIsPositive(false);

            $this->save($result->toArray());
        }
    }
}
