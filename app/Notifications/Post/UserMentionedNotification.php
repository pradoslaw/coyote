<?php

namespace Coyote\Notifications\Post;

class UserMentionedNotification extends SubmittedNotification
{
    const ID = \Coyote\Notification::POST_LOGIN;

    /**
     * @return string
     */
    protected function getMailSubject(): string
    {
        return $this->getSender() . ' wspomniał o Tobie w poście na forum';
    }

    /**
     * @return string
     */
    protected function getMailView(): string
    {
        return 'emails.notifications.post.login';
    }
}
