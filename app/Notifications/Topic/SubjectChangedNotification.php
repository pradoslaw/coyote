<?php

namespace Coyote\Notifications\Topic;

use Coyote\Notification;
use Coyote\Services\UrlBuilder;
use Coyote\Topic;
use Coyote\User;
use Illuminate\Notifications\Messages\MailMessage;

class SubjectChangedNotification extends AbstractNotification
{
    const ID = Notification::TOPIC_SUBJECT;

    /**
     * @var string
     */
    private $originalSubject;

    /**
     * @param mixed $originalSubject
     * @return $this
     */
    public function setOriginalSubject($originalSubject)
    {
        $this->originalSubject = $originalSubject;

        return $this;
    }

    /**
     * @param User $user
     * @return array
     */
    public function toDatabase($user)
    {
        return [
            'object_id'     => $this->objectId(),
            'user_id'       => $user->id,
            'type_id'       => static::ID,
            'subject'       => $this->topic->title,
            'excerpt'       => $this->originalSubject,
            'url'           => UrlBuilder::topic($this->topic),
            'id'            => $this->id,
            'content_type'  => Topic::class,
            'content_id'    => $this->topic->id
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail()
    {
        return (new MailMessage())
            ->subject($this->getMailSubject())
            ->view('emails.notifications.topic.subject', [
                'sender'        => $this->notifier->name,
                'subject'       => link_to($this->notificationUrl(), $this->topic->title),
                'original'      => $this->originalSubject
            ]);
    }

    /**
     * @return string
     */
    public function getMailSubject(): string
    {
        return 'Tytuł wątku został zmieniony';
    }
}
