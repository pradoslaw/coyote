<?php

namespace Coyote\Notifications\Post;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class AcceptedNotification extends AbstractNotification implements ShouldQueue
{
    const ID = \Coyote\Notification::POST_ACCEPT;

    /**
     * Get the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail()
    {
        return (new MailMessage)
            ->subject($this->getMailSubject())
            ->line(sprintf('%s zaakceptował Twój post w wątku <b>%s</b>', $this->notifier->name, $this->post->topic->title))
            ->action('Zobacz post', url($this->redirectionUrl()));
    }

    /**
     * @return string
     */
    protected function getMailSubject(): string
    {
        return $this->notifier->name . ' zaakceptował(a) Twój post';
    }
}
