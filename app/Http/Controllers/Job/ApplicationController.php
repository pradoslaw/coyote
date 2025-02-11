<?php
namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Requests\ApplicationRequest;
use Coyote\Job;
use Coyote\Job\Application;
use Coyote\Notifications\Job\ApplicationConfirmationNotification;
use Coyote\Notifications\Job\ApplicationSentNotification;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Objects\Application as Stream_Application;
use Coyote\Services\Stream\Objects\Job as Stream_Job;
use Coyote\Services\UrlBuilder;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function submit(Job $job): View
    {
        abort_if(!$job->enable_apply, 404);

        $this->breadcrumb->pushMany([
            'Praca'                          => route('job.home'),
            $job->title                      => UrlBuilder::job($job, true),
            'Aplikuj na to stanowisko pracy' => route('job.application', ['job' => $job]),
        ]);

        $application = new Application();

        if ($this->userId) {
            $application->email = $this->auth->email;
            $application->github = $this->auth->github;
        }

        // set default message
        $application->text = view('job.partials.application', ['job' => $job])->render();

        if ($this->getSetting('job.application')) {
            $application->forceFill((array)json_decode($this->getSetting('job.application')));
        }

        return $this->view('job.application', [
            'job'         => $job,
            'application' => $application,
            'subscribed'  => $this->userId ? $job->subscribers()->forUser($this->userId)->exists() : false,
        ]);
    }

    public function save(Job $job, ApplicationRequest $request): string
    {
        $application = $this->transaction(function () use ($job, $request) {
            $target = (new Stream_Job)->map($job);

            /** @var Application $application */
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

    public function upload(Request $request): JsonResponse
    {
        $this->validate($request, [
            // only 5 MB file size limit. otherwise postfix may not handle it properly.
            'cv' => 'max:' . (5 * 1024) . '|mimes:pdf,doc,docx,rtf',
        ]);

        $filename = uniqid() . '_' . Str::ascii($request->file('cv')->getClientOriginalName());
        $request->file('cv')->storeAs('cv', $filename, 'local');

        return response()->json([
            'filename' => $filename,
            'name'     => $request->file('cv')->getClientOriginalName(),
        ]);
    }

    public function downloadApplication(FilesystemManager $filesystem, Job $job, $id)
    {
        abort_if($job->user_id !== $this->userId, 403);

        /** @var Application $application */
        $application = $job->applications()->find($id);

        return $filesystem->disk('local')->download('cv/' . $application->cv, $application->realFilename());
    }
}
