<?php

namespace Coyote\Notifications\Post\Comment;

use Coyote\Notifications\Post\CommentedNotification;
use Illuminate\Notifications\Messages\MailMessage;

class UserMentionedNotification extends CommentedNotification
{
    const ID = \Coyote\Notification::POST_COMMENT_LOGIN;

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
                    '<strong>%s</strong> wspomniał o Tobie w komentarzu do posta w wątku: <strong>%s</strong>',
                    $this->notifier->name,
                    $this->post->topic->title
                )
            )
            ->line('<hr>')
            ->line($this->comment->html)
            ->line('<hr>')
            ->action('Zobacz komentarz', url($this->redirectionUrl()));
    }

    /**
     * @return string
     */
    protected function getMailSubject(): string
    {
        return $this->notifier->name . ' wspomniał(a) o Tobie w komentarzu w wątku: ' . $this->post->topic->title;
    }
}
