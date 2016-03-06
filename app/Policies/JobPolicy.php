<?php

namespace Coyote\Policies;

use Coyote\Job;
use Coyote\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class JobPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @param Job $job
     * @return bool
     */
    public function update(User $user, Job $job)
    {
        return $user->id === $job->user_id || $user->ability('job-update');
    }

    /**
     * @param User $user
     * @param Job $job
     * @return bool
     */
    public function delete(User $user, Job $job)
    {
        return $user->id === $job->user_id || $user->ability('job-delete');
    }
}
