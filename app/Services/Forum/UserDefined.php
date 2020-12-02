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
    private ForumRepository $forum;

    /**
     * @var Cache
     */
    private Cache $cache;

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
    public function allowedForums(?User $user): array
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

    public function followers(User $user): array
    {
        return $this->cache->remember('followers:' . $user->id, now()->addMonth(), function () use ($user) {
            return $user->relations()->get(['related_user_id AS user_id', 'is_blocked'])->values()->toArray();
        });
    }
}
