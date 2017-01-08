<?php

namespace Coyote\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    /**
     * The password reset token.
     *
     * @var string
     */
    public $token;

    /**
     * Create a notification instance.
     *
     * @param  string  $token
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's channels.
     *
     * @return array|string
     */
    public function via()
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail()
    {
        return (new MailMessage)
            ->subject('Ustaw nowe hasło w serwisie 4programmers.net')
            ->line('Przesyłamy tego e-maila gdyż skorzystałeś z formularza przypominania hasła.')
            ->action('Ustaw hasło', url('password/reset', $this->token))
            ->line('Jeżeli nie skorzystałeś z formularza przypominania hasła, prosimy o zignorowanie tego e-maila.');
    }
}
