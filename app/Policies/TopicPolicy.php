<?php

namespace Coyote\Policies;

use Coyote\Topic;
use Coyote\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Access\Gate;

class TopicPolicy
{
    use HandlesAuthorization;

    /**
     * @var Gate
     */
    private $gate;

    /**
     * TopicPolicy constructor.
     * @param Gate $gate
     */
    public function __construct(Gate $gate)
    {
        $this->gate = $gate;
    }

    /**
     * @param User|null $user
     * @param Topic $topic
     * @return bool
     */
    public function write(?User $user, Topic $topic)
    {
        // users with permissions can reply in locked topic
        return !$topic->is_locked ? true : ($user !== null ? $user->can('update', $topic->forum) : false);
    }
}

