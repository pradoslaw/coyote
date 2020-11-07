<?php

namespace Coyote\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use \Coyote\Events\SuccessfulLogin as SuccessfulLoginEvent;
use Jenssegers\Agent\Agent;

class SuccessfulLogin extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(SuccessfulLoginEvent $event)
    {
        $agent = new Agent();
        $agent->setUserAgent($event->browser);

        $this->with([
            'ip' => $event->ip,
            'host' => gethostbyaddr($event->ip),
            'browser' => $agent->browser(),
            'platform' => $agent->platform()
        ])
        ->with($event->user->toArray());
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('Powiadomienie o logowaniu z nowego urządzenia')
            ->view('emails.auth.successful');
    }
}
