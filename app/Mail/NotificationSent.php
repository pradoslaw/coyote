<?php

namespace Coyote\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotificationSent extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @var string
     */
    public $view;

    /**
     * NotificationSent constructor.
     * @param string $view
     * @param array $data
     */
    public function __construct(string $view, array $data = [])
    {
        $this->view = $view;
        $this->viewData = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('no-reply@4programmers.net', config('app.name'))->view($this->view);
    }
}
