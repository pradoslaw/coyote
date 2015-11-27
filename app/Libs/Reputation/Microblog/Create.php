<?php

namespace Coyote\Reputation\Microblog;

use Coyote\Reputation\ReputationInterface;

/**
 * Class Create
 * @package Coyote\Reputation\Microblog
 */
class Create extends Microblog implements ReputationInterface
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
}
