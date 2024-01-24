<?php
namespace Coyote\Services\Reputation\Microblog;

use Coyote\Services\Reputation\ReputationInterface;

class Vote extends Microblog implements ReputationInterface
{
    const ID = \Coyote\Reputation::MICROBLOG_VOTE;
}
