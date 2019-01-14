<?php

namespace Coyote\Listeners;

use Coyote\Events\SuccessfulLogin;
use Coyote\Repositories\Contracts\StreamRepositoryInterface as StreamRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Mail\Message;
use Jenssegers\Agent\Agent;

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
     * @var Agent
     */
    private $agent;

    /**
     * @param Mailer $mailer
     * @param StreamRepository $stream
     * @param Agent $agent
     */
    public function __construct(Mailer $mailer, StreamRepository $stream, Agent $agent)
    {
        $this->mailer = $mailer;
        $this->agent = $agent;
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
        $this->agent->setUserAgent($event->browser);

        $data = array_merge(
            $event->user->toArray(),
            [
                'ip' => $event->ip,
                'host' => gethostbyaddr($event->ip),
                'browser' => $this->agent->browser(),
                'platform' => $this->agent->platform()
            ]
        );

        $user = $event->user;

        $this->mailer->send('emails.auth.successful', $data, function (Message $message) use ($user) {
            $message->to($user->email);
            $message->subject('Powiadomienie o logowaniu z nowego urządzenia');
        });
    }
}
