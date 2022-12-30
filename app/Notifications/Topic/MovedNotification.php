<?php

namespace Coyote\Notifications\Topic;

use Coyote\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class MovedNotification extends AbstractNotification
{
    const ID = Notification::TOPIC_MOVE;

    /**
     * Get the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail()
    {
        return (new MailMessage())
            ->subject($this->getMailSubject())
            ->view('emails.notifications.topic.move', [
                'sender'        => $this->notifier->name,
                'title'         => link_to($this->redirectionUrl(), htmlentities($this->topic->title)),
                'reason_name'   => $this->getReasonName(),
                'reason_text'   => $this->getReasonText(),
                'forum'         => $this->topic->forum->name
            ]);
    }

    /**
     * @return string
     */
    public function getMailSubject(): string
    {
        return 'Wątek został przeniesiony';
    }
}
