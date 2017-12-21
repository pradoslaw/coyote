<?php

namespace Coyote\Listeners;

use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;

class SendLockoutEmail
{
    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @var UserRepository
     */
    protected $user;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @param Mailer $mailer
     * @param UserRepository $user
     */
    public function __construct(Mailer $mailer, UserRepository $user)
    {
        $this->mailer = $mailer;
        $this->user = $user;
    }

    /**
     * Handle the event.
     *
     * @param  Lockout  $event
     * @return void
     */
    public function handle(Lockout $event)
    {
        $this->request = $event->request;
        $user = $this->user->findByName($this->request->input('name'));

        if (!empty($user) && $user->is_confirm && $user->alert_failure) {
            $this->sendLockoutEmail($user);
        }
    }

    /**
     * @param User $user
     */
    protected function sendLockoutEmail(User $user)
    {
        $data = array_merge(
            $user->toArray(),
            ['ip' => $this->request->ip(), 'host' => $this->request->getClientHost()]
        );

        $this->mailer->send('emails.auth.lockout', $data, function (Message $message) use ($user) {
            $message->to($user->email);
            $message->subject('Powiadomienie o nieudanym logowaniu na Twoje konto');
        });
    }
}
