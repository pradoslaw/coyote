<?php

namespace Coyote\Notifications\Topic;

use Coyote\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DeletedNotification extends AbstractNotification
{
    const ID = Notification::TOPIC_DELETE;

    /**
     * Get the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail()
    {
        return (new MailMessage())
            ->subject($this->getMailSubject())
            ->view('emails.notifications.topic.delete', [
                'sender'        => $this->notifier->name,
                'title'         => link_to($this->notificationUrl(), $this->topic->title),
                'reason_name'   => $this->getReasonName(),
                'reason_text'   => $this->getReasonText()
            ]);
    }

    protected function getMailSubject(): string
    {
        return "Wątek został usunięty przez {$this->notifier->name}";
    }
}
