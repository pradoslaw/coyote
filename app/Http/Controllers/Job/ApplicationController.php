<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Factories\MailFactory;
use Coyote\Http\Forms\Job\ApplicationForm;
use Coyote\Job;
use Coyote\Notifications\Job\ApplicationConfirmationNotification;
use Coyote\Notifications\Job\ApplicationSentNotification;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Illuminate\Http\Request;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Objects\Job as Stream_Job;
use Coyote\Services\Stream\Objects\Application as Stream_Application;

class ApplicationController extends Controller
{
    use MailFactory;

    public function __construct()
    {
        parent::__construct();

        $this->middleware(
            function (Request $request, $next) {
                /** @var \Coyote\Job $job */
                $job = $request->route('job');
                abort_if($job->applications()->forGuest($this->guestId)->exists(), 404);

                return $next($request);
            },
            ['except' => 'upload']
        );
    }

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

        /**
         * @var ApplicationForm $form
         */
        $form = $this->createForm(ApplicationForm::class);

        if ($this->userId) {
            $form->get('email')->setValue($this->auth->email);
            $form->get('github')->setValue($this->auth->github);
        }

        // set default message
        $form->get('text')->setValue(view('job.partials.application', compact('job')));

        if ($this->getSetting('job.application')) {
            $form->setData(json_decode($this->getSetting('job.application')));
        }

        return $this->view('job.application', compact('job', 'form'))->with(
            'subscribed',
            $this->userId ? $job->subscribers()->forUser($this->userId)->exists() : false
        );
    }

    /**
     * @param Job $job
     * @param ApplicationForm $form
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save($job, ApplicationForm $form)
    {
        $data = $form->all() + ['guest_id' => $this->guestId];

        $this->transaction(function () use ($job, $form, $data) {
            $target = (new Stream_Job)->map($job);

            /** @var \Coyote\Job\Application $application */
            $application = $job->applications()->create($data);

            $this->setSetting('job.application', $form->get('remember')->isChecked() ? $form->toJson() : '');

            $job->notify((new ApplicationSentNotification($application))->delay(10));
            $application->notify(new ApplicationConfirmationNotification());

            stream(Stream_Create::class, new Stream_Application(['displayName' => $data['name']]), $target);
        });

        return redirect()
            ->route('job.offer', [$job->id, $job->slug])
            ->with('success', 'Zgłoszenie zostało prawidłowo wysłane.');
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

        $filename = uniqid() . '_' . $request->file('cv')->getClientOriginalName();
        $request->file('cv')->storeAs('cv', $filename, 'local');

        return response()->json([
            'filename' => $filename,
            'name' => $request->file('cv')->getClientOriginalName()
        ]);
    }
}
