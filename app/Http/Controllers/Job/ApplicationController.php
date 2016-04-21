<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Forms\Job\ApplicationForm;
use Coyote\Job;

class ApplicationController extends Controller
{
    public function submit(Job $job)
    {
        $this->breadcrumb->push($job->title, route('job.offer', [$job->id, $job->path]));
        $this->breadcrumb->push('Aplikuj na to stanowisko pracy');

        $form = $this->createForm(ApplicationForm::class);

        if ($this->userId) {
            $form->email->setValue(auth()->user()->email);
        }
        return $this->view('job.application')->with(compact('job', 'form'));
    }

    public function save(Job $job, ApplicationForm $form)
    {
        dd($form->all(), $form->getField('email')->getValue());
        return back()->with('success', 'Zgłoszenie zostało prawidłowo wysłane.');
    }
}
