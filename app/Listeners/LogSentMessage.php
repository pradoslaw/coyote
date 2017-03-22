<?php

namespace Coyote\Listeners;

use Coyote\Repositories\Contracts\MailRepositoryInterface as MailRepository;
use Illuminate\Mail\Events\MessageSending;

class LogSentMessage
{
    /**
     * @var MailRepository
     */
    protected $mail;

    /**
     * @param MailRepository $mail
     */
    public function __construct(MailRepository $mail)
    {
        $this->mail = $mail;
    }

    /**
     * Handle the event.
     *
     * @param  MessageSending  $event
     * @return void
     */
    public function handle(MessageSending $event)
    {
        $this->mail->create([
            'subject'       => $event->message->getSubject(),
            'email'         => key($event->message->getTo())
        ]);
    }
}
