<?php
namespace Coyote\Policies;

use Coyote\Topic;
use Coyote\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Access\Gate;

class TopicPolicy
{
    use HandlesAuthorization;

    public function __construct(private Gate $gate)
    {
    }

    public function write(?User $user, Topic $topic): bool
    {
        // users with permissions can reply in locked topic
        if (!$topic->is_locked) {
            return true;
        }
        if ($user !== null) {
            return $user->can('update', $topic->forum);
        }
        return false;
    }
}
