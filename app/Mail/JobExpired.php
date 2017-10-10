<?php

namespace Coyote\Mail;

use Coyote\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class JobExpired extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var Job
     */
    protected $job;

    /**
     * @param Job $job
     */
    public function __construct(Job $job)
    {
        $this->job = $job;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->subject(sprintf('Twoje ogłoszenie "%s" wygasło.', $this->job->title));

        return $this->view('emails.job.expired', $this->job->toArray() + ['applications' => $this->job->applications->count()]);
    }
}
