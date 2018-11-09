<?php

namespace Coyote\Notifications\Post\Comment;

use Coyote\Notifications\Post\CommentedNotification;

class UserMentionedNotification extends CommentedNotification
{
    const ID = \Coyote\Notification::POST_COMMENT_LOGIN;

    /**
     * @return string
     */
    protected function getMailSubject(): string
    {
        return $this->notifier->name . ' wspomniał o Tobie w komentarzu na forum';
    }

    /**
     * @return string
     */
    protected function getMailView(): string
    {
        return 'emails.notifications.post.comment.login';
    }
}
