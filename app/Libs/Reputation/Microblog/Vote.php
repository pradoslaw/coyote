<?php

namespace Coyote\Reputation\Microblog;

use Coyote\Reputation\Reputation;
use Coyote\Reputation\ReputationInterface;

/**
 * Class Create
 * @package Coyote\Reputation\Microblog
 */
class Vote extends Reputation implements ReputationInterface
{
    const ID = \Coyote\Reputation::MICROBLOG_VOTE;
}
