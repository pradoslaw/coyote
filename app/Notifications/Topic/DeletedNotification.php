<?php

namespace Coyote\Notifications\Topic;

use Coyote\Notification;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Coyote\User;
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
                'subject'       => link_to(UrlBuilder::topic($this->topic), $this->topic->subject),
                'reason_name'   => $this->reasonName,
                'reason_text'   => $this->reasonText
            ]);
    }

    /**
     * @param User $user
     * @return array
     */
    public function toDatabase(User $user)
    {
        return [
            'object_id'     => $this->objectId(),
            'user_id'       => $user->id,
            'type_id'       => static::ID,
            'subject'       => $this->topic->subject,
            'excerpt'       => $this->reasonName,
            'url'           => UrlBuilder::topic($this->topic),
            'guid'          => $this->id
        ];
    }

    protected function getMailSubject(): string
    {
        return "Wątek został usunięty przez {$this->notifier->name}";
    }
}
