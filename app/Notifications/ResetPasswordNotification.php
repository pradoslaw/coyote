<?php
namespace Coyote\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    public function __construct(public string $token)
    {
    }

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->subject('Ustaw nowe hasło w serwisie 4programmers.net')
            ->line('Przesyłamy tego e-maila gdyż skorzystałeś z formularza przypominania hasła.')
            ->action('Ustaw hasło', url('password/reset', $this->token))
            ->line('Jeżeli nie skorzystałeś z formularza przypominania hasła, prosimy o zignorowanie tego e-maila.');
    }
}
