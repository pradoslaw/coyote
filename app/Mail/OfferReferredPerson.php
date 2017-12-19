<?php

namespace Coyote\Mail;

use Coyote\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class OfferReferredPerson extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @var Job
     */
    public $job;

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
        return $this
            ->subject("Zostałeś polecony na stanowisko {$this->job->title}")
            ->replyTo($this->job->email ?: $this->job->user->name, $this->job->user->name)
            ->view('emails.job.refer_person');
    }
}
