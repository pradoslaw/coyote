<?php

namespace Coyote\Listeners;

use Coyote\Events\SuccessfulLogin;
use Coyote\Repositories\Contracts\StreamRepositoryInterface as StreamRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Mail\Mailer;
use Coyote\Mail\SuccessfulLogin as Mailable;

class SendSuccessfulLoginEmail implements ShouldQueue
{
    /**
     * @var StreamRepository
     */
    private $stream;

    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * @param Mailer $mailer
     * @param StreamRepository $stream
     */
    public function __construct(Mailer $mailer, StreamRepository $stream)
    {
        $this->mailer = $mailer;
        $this->stream = $stream;
    }

    /**
     * Handle the event.
     *
     * @param  SuccessfulLogin  $event
     * @return void
     */
    public function handle(SuccessfulLogin $event)
    {
        if ($this->shouldSendEmail($event)) {
            $this->sendSuccessfulLoginEmail($event);
        }
    }

    /**
     * @param SuccessfulLogin $event
     * @return bool
     */
    private function shouldSendEmail(SuccessfulLogin $event)
    {
        // first, check if IP is setup. if not, this means that user logged in for the first time.
        // second: if current ip is same as previous, skip it.
        if (empty($event->user->ip)
            || $event->ip == $event->user->ip
                || !$event->user->is_confirm
                    || !$event->user->alert_login) {
            return false;
        }

        return ! $this->stream->hasLoggedBefore($event->user->id, $event->ip, $event->browser);
    }

    /**
     * @param SuccessfulLogin $event
     */
    private function sendSuccessfulLoginEmail(SuccessfulLogin $event)
    {
        $this->mailer->to($event->user)->send(new Mailable($event));
    }
}
