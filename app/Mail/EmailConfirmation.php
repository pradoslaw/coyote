<?php

namespace Coyote\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param string $url
     */
    public function __construct(string $url)
    {
        $this->viewData['url'] = $url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Prosimy o potwierdzenie nowego adresu e-mail')->view('emails.user.change_email');
    }
}
