<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;
use Coyote\Job;
use Coyote\Services\Job\Draft;
use Coyote\Services\Job\Loader;

class RenewController extends Controller
{
    /**
     * @param Loader $loader
     * @param Job $job
     * @param Draft $draft
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Loader $loader, Job $job, Draft $draft)
    {
        abort_unless($job->is_expired, 404);

        $job = $loader->init($job);

        unset($job->id);
        $job->exists = false; // new job offer

        $job->user_id = $this->userId;

        // reset all plan values
        $job->is_boost = $job->is_publish = $job->is_ads = $job->is_on_top = $job->is_highlight = false;
        // reset views counter
        $job->views = 1;

        $this->authorize('update', $job);
        $this->authorize('update', $job->firm);

        $draft->put(Job::class, $job);

        return redirect()->route('job.submit');
    }
}
