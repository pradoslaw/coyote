<?php

namespace Coyote\Mail;

use Illuminate\Mail\Mailable;

class PlanReminder extends Mailable
{
    /**
     * @var \Coyote\Job[]
     */
    public $jobs;

    /**
     * @param \Coyote\Job[] $jobs
     */
    public function __construct($jobs)
    {
        $this->jobs = $jobs;

        $this->subject('Skorzystaj z opcji promowania swojego ogłoszenia');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.job.reminder');
    }
}
