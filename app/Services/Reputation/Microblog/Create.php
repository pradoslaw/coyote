<?php

namespace Coyote\Services\Reputation\Microblog;

/**
 * Class Create
 */
class Create extends Microblog
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
