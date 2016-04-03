<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Events\JobWasDeleted;
use Coyote\Http\Controllers\Controller;
use Coyote\Job;

class DeleteController extends Controller
{
    /**
     * @param Job $job
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function index(Job $job)
    {
        \DB::transaction(function () use ($job) {
            $job->delete();
            event(new JobWasDeleted($job));
        });

        return redirect()->route('job.home')->with('success', 'Oferta pracy została usunięta.');
    }
}