<?php

namespace Coyote\Notifications\Microblog;

use Coyote\Microblog;
use Coyote\Services\UrlBuilder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SubmittedNotification extends AbstractNotification implements ShouldQueue
{
    const ID = \Coyote\Notification::MICROBLOG_SUBSCRIBER;

    /**
     * @param \Coyote\User $user
     * @return array
     */
    public function toDatabase($user)
    {
        return [
            'object_id'     => $this->objectId(),
            'user_id'       => $user->id,
            'type_id'       => static::ID,
            'subject'       => excerpt($this->microblog->html),
            'url'           => UrlBuilder::microblog($this->microblog),
            'id'            => $this->id,
            'content_type'  => Microblog::class,
            'content_id'    => $this->microblog->id
        ];
    }

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
                    '<strong>%s</strong> dodał wpis na mikroblogu: <strong>%s</strong>',
                    $this->notifier->name,
                    excerpt($this->microblog->html)
                )
            )
            ->action('Zobacz wpis', url($this->notificationUrl()))
            ->line('Dostajesz to powiadomienie, ponieważ obserwujesz jego autora.');
    }

    /**
     * @return string
     */
    protected function getMailSubject(): string
    {
        return "{$this->notifier->name} dodał wpis na mikroblogu";
    }
}
