<?php

namespace Coyote\Mail;

use Coyote\Job;
use Illuminate\Mail\Mailable;

class ApplicationSent extends Mailable
{
    /**
     * @var Job\Application
     */
    private $application;

    /**
     * @var Job
     */
    private $job;

    /**
     * ApplicationSent constructor.
     * @param Job\Application $application
     * @param Job $job
     */
    public function __construct(Job\Application $application, Job $job)
    {
        $this->application = $application;
        $this->job = $job;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if ($this->application->cv) {
            $path = realpath(storage_path('app/cv/' . $this->application->cv));
            $filename = basename($path);

            $name = explode('_', $filename, 2)[1];
            $this->attach($path, ['as' => $name]);
        }

        return $this
            ->view('emails.job.application', $this->application->toArray())
            ->replyTo($this->application->email, $this->application->name)
            ->subject(sprintf('[%s] %s', $this->application->name, $this->job->title));
    }
}
