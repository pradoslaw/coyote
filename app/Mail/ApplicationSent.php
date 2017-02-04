<?php

namespace Coyote\Mail;

use Coyote\Http\Forms\Job\ApplicationForm;
use Coyote\Job;
use Illuminate\Mail\Mailable;

class ApplicationSent extends Mailable
{
    /**
     * @var ApplicationForm
     */
    private $form;

    /**
     * @var Job
     */
    private $job;

    /**
     * @param ApplicationForm $form
     * @param Job $job
     */
    public function __construct(ApplicationForm $form, Job $job)
    {
        $this->form = $form;
        $this->job = $job;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if ($this->form->get('cc')->isChecked()) {
            $this->cc($this->form->get('email')->getValue());
        }

        if ($this->form->get('cv')->getValue()) {
            $name = explode('_', $this->form->get('cv')->getValue(), 2)[1];
            $this->attach(storage_path('app/tmp/' . $this->form->get('cv')->getValue()), ['as' => $name]);
        }

        return $this
            ->view('emails.job.application', $this->form->all())
            ->to($this->form->get('email')->getValue())
            ->replyTo($this->form->get('email')->getValue(), $this->form->get('name')->getValue())
            ->subject(sprintf('[%s] %s', $this->form->get('name')->getValue(), $this->job->title));
    }
}
