<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Events\JobWasDeleted;
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
        \DB::transaction(function () use ($job) {
            $job->delete();
            event(new JobWasDeleted($job));

            stream(Stream_Delete::class, (new Stream_Job)->map($job));
        });

        return redirect()->route('job.home')->with('success', 'Oferta pracy została usunięta.');
    }
}
