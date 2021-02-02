<?php

namespace Coyote\Notifications\Microblog;

use Coyote\Microblog;
use Coyote\Services\UrlBuilder;
use Illuminate\Notifications\Messages\MailMessage;

class CommentedNotification extends SubmittedNotification
{
    const ID = \Coyote\Notification::MICROBLOG_COMMENT;

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
            'subject'       => excerpt($this->microblog->parent->html), // original excerpt of parent entry
            'excerpt'       => excerpt($this->microblog->html),
            'url'           => UrlBuilder::microblogComment($this->microblog),
            'id'            => $this->id,
            'content_type'  => Microblog::class,
            'content_id'    => $this->microblog->parent_id
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
                    '<strong>%s</strong> dodał nowy komentarz we wpisie na mikroblogu: <strong>%s</strong>',
                    $this->notifier->name,
                    excerpt($this->microblog->parent->html)
                )
            )
            ->action('Zobacz komentarz', url($this->notificationUrl()))
            ->line('Dostajesz to powiadomienie, ponieważ obserwujesz ten wpis lub śledzisz jego autora.');
    }

    /**
     * @return string
     */
    protected function getMailSubject(): string
    {
        return "{$this->notifier->name} dodał komentarz do wpisu na mikroblogu";
    }
}
