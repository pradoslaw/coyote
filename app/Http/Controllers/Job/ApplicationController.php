<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Factories\MailFactory;
use Coyote\Http\Forms\Job\ApplicationForm;
use Coyote\Job;
use Coyote\Mail\ApplicationSent;
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
                abort_if($job->hasApplied($this->userId, $this->sessionId), 404);

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
            $job->title                         => route('job.offer', [$job->id, $job->slug]),
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
        $filesystem = app('filesystem')->disk('local');
        $data = $form->all() + ['user_id' => $this->userId, 'session_id' => $this->sessionId];

        $mail = new ApplicationSent($form, $job);

        $this->transaction(function () use ($job, $data, $mail) {
            $target = (new Stream_Job)->map($job);

            $job->applications()->create($data);
            $this->getMailFactory()->send($mail);

            stream(Stream_Create::class, new Stream_Application(['displayName' => $data['name']]), $target);
        });

        if ($form->get('cv')->getValue()) {
            $filesystem->delete('tmp/' . $form->get('cv')->getValue());
        }

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
            'cv'             => 'max:' . (config('filesystems.upload_max_size') * 1024) . '|mimes:pdf,doc,docx,rtf'
        ]);

        $filename = uniqid() . '_' . $request->file('cv')->getClientOriginalName();
        $request->file('cv')->storeAs('tmp', $filename, 'local');

        return response()->json([
            'filename' => $filename,
            'name' => $request->file('cv')->getClientOriginalName()
        ]);
    }
}
