<?php

namespace Coyote\Notifications\Topic;

use Coyote\Notification;
use Coyote\Services\UrlBuilder;
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
                'subject'       => link_to($this->notificationUrl(), $this->topic->subject),
                'reason_name'   => $this->reasonName,
                'reason_text'   => $this->reasonText
            ]);
    }

    protected function getMailSubject(): string
    {
        return "Wątek został usunięty przez {$this->notifier->name}";
    }
}
