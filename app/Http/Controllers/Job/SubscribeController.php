<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;
use Coyote\Job;

class SubscribeController extends Controller
{
    /**
     * @param Job $job
     */
    public function index(Job $job)
    {
        $subscribe = $job->subscribers()->forUser($this->userId)->first();

        if (!$subscribe) {
            $job->subscribers()->create(['user_id' => $this->userId]);
        } else {
            $subscribe->delete();
        }
    }
}
