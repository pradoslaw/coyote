<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Factories\MailFactory;
use Coyote\Http\Requests\ApplicationRequest;
use Coyote\Job;
use Coyote\Notifications\Job\ApplicationConfirmationNotification;
use Coyote\Notifications\Job\ApplicationSentNotification;
use Coyote\Services\UrlBuilder;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Http\Request;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Objects\Job as Stream_Job;
use Coyote\Services\Stream\Objects\Application as Stream_Application;
use Illuminate\Support\Str;

class ApplicationController extends Controller
{
    use MailFactory;

    /**
     * @param Job $job
     * @return \Illuminate\View\View
     */
    public function submit($job)
    {
        abort_if(!$job->enable_apply, 404);

        $this->breadcrumb->push([
            'Praca'                             => route('job.home'),
            $job->title                         => UrlBuilder::job($job),
            'Aplikuj na to stanowisko pracy'    => null
        ]);

        $application = new Job\Application();

        if ($this->userId) {
            $application->email = $this->auth->email;
            $application->github = $this->auth->github;
        }

        // set default message
        $application->text = view('job.partials.application', compact('job'))->render();

        if ($this->getSetting('job.application')) {
            $application->forceFill((array) json_decode($this->getSetting('job.application')));
        }

        return $this->view('job.application', compact('job', 'application'))->with([
            'subscribed' => $this->userId ? $job->subscribers()->forUser($this->userId)->exists() : false
        ]);
    }

    /**
     * @param Job $job
     * @param ApplicationRequest $request
     * @return string
     */
    public function save(Job $job, ApplicationRequest $request)
    {
        $application = $this->transaction(function () use ($job, $request) {
            $target = (new Stream_Job)->map($job);

            /** @var \Coyote\Job\Application $application */
            $application = $job->applications()->create($request->all() + ['guest_id' => $this->guestId]);
            $this->setSetting('job.application', $request->get('remember') ? json_encode($request->all()) : '');

            stream(Stream_Create::class, new Stream_Application(['displayName' => $request->input('name')]), $target);

            return $application;
        });

        $job->notify(new ApplicationSentNotification($application));
        $application->notify(new ApplicationConfirmationNotification());

        session()->flash('success', 'Zgłoszenie zostało prawidłowo wysłane.');

        return UrlBuilder::job($job);
    }

    /**
     * Upload cv/resume
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        $this->validate($request, [
            // only 5 MB file size limit. otherwise postfix may not handle it properly.
            'cv'             => 'max:' . (5 * 1024) . '|mimes:pdf,doc,docx,rtf'
        ]);

        $filename = uniqid() . '_' . Str::ascii($request->file('cv')->getClientOriginalName());
        $request->file('cv')->storeAs('cv', $filename, 'local');

        return response()->json([
            'filename' => $filename,
            'name' => $request->file('cv')->getClientOriginalName()
        ]);
    }

    /**
     * @param FilesystemManager $filesystem
     * @param Job $job
     * @param $id
     * @return mixed
     */
    public function downloadApplication(FilesystemManager $filesystem, Job $job, $id)
    {
        abort_if($job->user_id !== $this->userId, 403);

        /** @var \Coyote\Job\Application $application */
        $application = $job->applications()->find($id);

        return $filesystem->disk('local')->download('cv/' . $application->cv, $application->realFilename());
    }
}
