<?php

namespace Coyote\Reputation\Microblog;

use Coyote\Reputation\ReputationInterface;

/**
 * Class Create
 * @package Coyote\Reputation\Microblog
 */
class Vote extends Microblog implements ReputationInterface
{
    const ID = \Coyote\Reputation::MICROBLOG_VOTE;
}
