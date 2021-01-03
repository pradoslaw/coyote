<?php

namespace Coyote\Notifications\Post;

use Illuminate\Notifications\Messages\MailMessage;

class UserMentionedNotification extends SubmittedNotification
{
    const ID = \Coyote\Notification::POST_LOGIN;

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
                    '<strong>%s</strong> wspomniał o Tobie w poście w wątku: <strong>%s</strong>',
                    $this->getSender(),
                    $this->post->topic->title
                )
            )
            ->line('<hr>')
            ->line($this->post->html)
            ->line('<hr>')
            ->action('Zobacz post', url($this->notificationUrl()));
    }

    /**
     * @return string
     */
    protected function getMailSubject(): string
    {
        return $this->getSender() . ' wspomniał(a) o Tobie w poście na forum';
    }
}
