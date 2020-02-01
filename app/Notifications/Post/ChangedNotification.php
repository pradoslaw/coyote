<?php

namespace Coyote\Notifications\Post;

use Illuminate\Notifications\Messages\MailMessage;

class ChangedNotification extends SubmittedNotification
{
    const ID = \Coyote\Notification::POST_EDIT;

    /**
     * Get the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail()
    {
        return (new MailMessage())
            ->subject($this->getMailSubject())
            ->line(
                sprintf(
                    'Informujemy, ze <strong>%s</strong> zmodyfikował post, który obserwujesz w wątku: %s.',
                    $this->notifier->name,
                    $this->post->topic->subject
                )
            )
            ->action('Zobacz post', url($this->notificationUrl()))
            ->line('Dostajesz to powiadomienie, ponieważ obserwujesz ten post.');
    }

    /**
     * @return string
     */
    protected function getMailSubject(): string
    {
        return $this->getSender() . ' zmodyfikował post, który obserwujesz';
    }
}
