<?php

namespace Coyote\Notifications\Microblog;

use Coyote\Services\UrlBuilder\UrlBuilder;
use Coyote\User;
use Illuminate\Notifications\Messages\MailMessage;

class UserMentionedNotification extends AbstractNotification
{
    const ID = \Coyote\Notification::MICROBLOG_LOGIN;

    /**
     * @param User $user
     * @return array
     */
    public function toDatabase($user)
    {
        $url = $this->microblog->parent_id ? UrlBuilder::microblogComment($this->microblog->parent, $this->microblog->id) : UrlBuilder::microblog($this->microblog);

        return [
            'object_id'     => $this->objectId(),
            'user_id'       => $user->id,
            'type_id'       => static::ID,
            'subject'       => excerpt($this->microblog->parent_id ? $this->microblog->parent->html : $this->microblog->html), // original excerpt of parent entry
            'excerpt'       => excerpt($this->microblog->html),
            'url'           => $url,
            'id'            => $this->id
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
            ->line(
                sprintf(
                    '<strong>%s</strong> użył Twojego loginu w treści wpisu mikrobloga: <strong>%s</strong>',
                    $this->notifier->name,
                    excerpt($this->microblog->html)
                )
            )
            ->action('Zobacz', url($this->notificationUrl()))
            ->line('Dostajesz to powiadomienie, ponieważ wynika to z ustawień Twojego konta.');
    }

    protected function getMailSubject(): string
    {
        return $this->notifier->name . ' wspomniał o Tobie na mikroblogu';
    }
}
