<?php

namespace Coyote\Reputation\Post;

use Coyote\Reputation\ReputationInterface;
use Coyote\Reputation\Reputation;

/**
 * Class Vote
 * @package Coyote\Reputation\Post
 */
class Vote extends Reputation implements ReputationInterface
{
    const ID = \Coyote\Reputation::POST_VOTE;

    /**
     * @param int $postId
     * @return $this
     */
    public function setPostId($postId)
    {
        $this->metadata['post_id'] = $postId;
        return $this;
    }
}
