<?php

namespace Coyote\Services\Reputation\Microblog;

use Coyote\Services\Reputation\ReputationInterface;

/**
 * Class Create
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
            $this->setPositive(false);

            $this->save($result->toArray());
        }
    }
}
