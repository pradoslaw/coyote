<?php
namespace Coyote\Services\Reputation\Post;

use Coyote\Services\Reputation\Reputation;
use Coyote\Services\Reputation\ReputationInterface;

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
