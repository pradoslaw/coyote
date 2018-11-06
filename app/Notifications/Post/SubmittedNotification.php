<?php

namespace Coyote\Notifications\Post;

use Coyote\Services\UrlBuilder\UrlBuilder;
use Illuminate\Notifications\Messages\MailMessage;

class SubmittedNotification extends AbstractNotification
{
    const ID = \Coyote\Notification::TOPIC_SUBSCRIBER;

    /**
     * Get the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail()
    {
        return (new MailMessage)
            ->subject($this->getMailSubject())
            ->view($this->getMailView(), [
                'sender'    => $this->notifier->name,
                'subject'   => link_to(UrlBuilder::post($this->post), $this->post->topic->subject),
                'text'      => app('parser.post')->parse($this->post->text)
            ]);
    }

    /**
     * @return string
     */
    protected function getMailSubject(): string
    {
        return $this->notifier->name . ' dodał(a) odpowiedź w wątku';
    }

    /**
     * @return string
     */
    protected function getMailView(): string
    {
        return 'emails.notifications.post.subscriber';
    }
}
