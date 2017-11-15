<?php

namespace Coyote\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class Mailing extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $subject;

    /**
     * @var string
     */
    public $text;

    /**
     * @param string $id
     * @param string $subject
     * @param string $text
     */
    public function __construct(string $id, string $subject, string $text)
    {
        $this->id = $id;
        $this->subject = $subject;
        $this->text = $text;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.mailing');
    }
}
