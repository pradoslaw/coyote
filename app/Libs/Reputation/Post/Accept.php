<?php

namespace Coyote\Reputation\Post;

use Coyote\Reputation\ReputationInterface;
use Coyote\Reputation\Reputation;

/**
 * Class Accept
 * @package Coyote\Reputation\Post
 */
class Accept extends Reputation implements ReputationInterface
{
    const ID = \Coyote\Reputation::POST_ACCEPT;

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
