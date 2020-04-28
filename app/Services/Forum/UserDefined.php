<?php

namespace Coyote\Services\Forum;

use Coyote\Repositories\Contracts\ForumRepositoryInterface as ForumRepository;
use Coyote\Repositories\Criteria\Forum\AccordingToUserOrder;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess;
use Coyote\User;
use Illuminate\Contracts\Cache\Repository as Cache;

class UserDefined
{
    /**
     * @var ForumRepository
     */
    private $forum;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @param ForumRepository $forum
     * @param Cache $cache
     */
    public function __construct(ForumRepository $forum, Cache $cache)
    {
        $this->forum = $forum;
        $this->cache = $cache;
    }

    /**
     * @param User|null $user
     * @return array
     */
    public function getAllowedForums(?User $user): array
    {
        $userId = $user->id ?? null;

        return $this->cache->tags('forum-order')->remember('forum-order:' . $userId, now()->addMonth(), function () use ($user, $userId) {
            // since repository is singleton, we have to reset previously set criteria to avoid duplicated them.
            $this->forum->resetCriteria();
            // make sure we don't skip criteria
            $this->forum->skipCriteria(false);

            $this->forum->pushCriteria(new OnlyThoseWithAccess($user));
            $this->forum->pushCriteria(new AccordingToUserOrder($userId, true));

            return $this->forum->list()->toArray();
        });
    }
}
