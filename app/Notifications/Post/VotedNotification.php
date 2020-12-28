<?php

namespace Coyote\Notifications\Post;

use Illuminate\Notifications\Messages\MailMessage;

class VotedNotification extends AbstractNotification
{
    const ID = \Coyote\Notification::POST_VOTE;

    /**
     * Get the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail()
    {
        return (new MailMessage)
            ->subject($this->getMailSubject())
            ->line(sprintf('%s docenił Twój post w wątku <b>%s</b>', $this->notifier->name, $this->post->topic->title))
            ->action('Zobacz post', url($this->notificationUrl()));
    }

    /**
     * @return string
     */
    protected function getMailSubject(): string
    {
        return $this->notifier->name . ' docenił(a) Twój post';
    }
}
