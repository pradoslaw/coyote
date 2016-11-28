<?php

namespace Coyote\Listeners;

use Coyote\Repositories\Contracts\SessionRepositoryInterface as SessionRepository;
use Coyote\Session;
use Coyote\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Mail\Message;
use Jenssegers\Agent\Agent;

class SendSuccessfulLoginEmail implements ShouldQueue
{
    /**
     * @var SessionRepository
     */
    private $session;

    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * @var Agent
     */
    private $agent;

    /**
     * SendSuccessfulLoginEmail constructor.
     * @param Mailer $mailer
     * @param SessionRepository $session
     * @param Agent $agent
     */
    public function __construct(Mailer $mailer, SessionRepository $session, Agent $agent)
    {
        $this->mailer = $mailer;
        $this->session = $session;
        $this->agent = $agent;
    }

    /**
     * Handle the event.
     *
     * @param  Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        /** @var \Coyote\User $event->user */
        if (!$event->user->alert_login || !$event->user->visits || !$event->user->is_confirm) {
            return;
        }

        $result = $this->session->findBy('user_id', $event->user->id);

        if (!empty($result) && ($result->ip != $event->user->ip)) {
            $this->sendSuccessfulLoginEmail($result, $event->user);
        }
    }

    /**
     * @param Session $session
     * @param User $user
     */
    private function sendSuccessfulLoginEmail(Session $session, User $user)
    {
        $this->agent->setUserAgent($session->browser);

        $data = array_merge(
            $user->toArray(),
            [
                'ip' => $session->ip,
                'host' => gethostbyaddr($session->ip),
                'browser' => $this->agent->browser(),
                'platform' => $this->agent->platform()
            ]
        );

        $this->mailer->send('emails.auth.successful', $data, function (Message $message) use ($user) {
            $message->to($user->email);
            $message->subject('Powiadomienie o udanym logowaniu na Twoje konto');
        });
    }
}
