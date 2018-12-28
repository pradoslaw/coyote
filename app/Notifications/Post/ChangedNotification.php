<?php

namespace Coyote\Notifications\Post;

class ChangedNotification extends SubmittedNotification
{
    /**
     * @return string
     */
    protected function getMailSubject(): string
    {
        return $this->getSender(). ' zmodyfikował post, który obserwujesz';
    }

    /**
     * @return string
     */
    protected function getMailView(): string
    {
        return 'emails.notifications.post.changed';
    }
}
