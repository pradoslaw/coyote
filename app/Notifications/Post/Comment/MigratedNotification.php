<?php

namespace Coyote\Notifications\Post\Comment;

use Coyote\Notifications\Post\AbstractNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class MigratedNotification extends AbstractNotification implements ShouldQueue
{
    const ID = \Coyote\Notification::POST_COMMENT_MIGRATED;

    /**
     * Get the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail()
    {
        return (new MailMessage)
            ->subject($this->getMailSubject())
            ->line(
                sprintf(
                    'Twój komentarz został zamieniony na post przez <strong>%s</strong>.',
                    $this->notifier->name
                )
            )
            ->line('<strong>Prosimy o prowadzenie dyskusji w postach!</strong>')
            ->line('Komentarze są jedynie dodatkiem na wypadek gdybyśmy musieli zwrócić uwagę na literówkę w poście, błędne formatowanie kodu itp.')
            ->action('Zobacz post', url($this->notificationUrl()))
            ->line('Jeżeli nie chcesz dostawać tego typu powiadomień, zmień ustawienia na swoim koncie użytkownika.');
    }

    /**
     * @return string
     */
    protected function getMailSubject(): string
    {
        return $this->notifier->name . ' zamienił komentarz na post w wątku: ' . $this->post->topic->title;
    }
}
