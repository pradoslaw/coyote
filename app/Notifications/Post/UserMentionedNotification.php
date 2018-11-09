<?php

namespace Coyote\Notifications\Post;

class UserMentionedNotification extends CommentedNotification
{
    const ID = \Coyote\Notification::POST_LOGIN;

    /**
     * @return string
     */
    protected function getMailSubject(): string
    {
        return $this->notifier->name . ' wspomniał o Tobie w poście na forum';
    }

    /**
     * @return string
     */
    protected function getMailView(): string
    {
        return 'emails.notifications.post.login';
    }
}
