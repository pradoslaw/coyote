<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Events\JobDeleted;
use Coyote\Events\JobDeleting;
use Coyote\Http\Controllers\Controller;
use Coyote\Job;
use Coyote\Services\Stream\Objects\Job as Stream_Job;
use Coyote\Services\Stream\Activities\Delete as Stream_Delete;

class DeleteController extends Controller
{
    /**
     * @param Job $job
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function index(Job $job)
    {
        $this->authorize('delete', $job);

        $this->transaction(function () use ($job) {
            event(new JobDeleting($job));
            $job->delete();

            stream(Stream_Delete::class, (new Stream_Job)->map($job));

            event(new JobDeleted($job));
        });

        return redirect()->route('job.home')->with('success', 'Oferta pracy została usunięta.');
    }
}
