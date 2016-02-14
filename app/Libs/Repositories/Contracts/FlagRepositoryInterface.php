<?php

namespace Coyote\Repositories\Contracts;

interface FlagRepositoryInterface extends RepositoryInterface
{
    /**
     * @param array $topicsId
     * @return mixed
     */
    public function takeForTopics(array $topicsId);

    /**
     * @param array $postsId
     * @return mixed
     */
    public function takeForPosts(array $postsId);
}
