<?php

namespace Coyote\Notifications\Post;

use Coyote\Post\Comment;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Coyote\User;
use Illuminate\Notifications\Messages\MailMessage;

class CommentedNotification extends AbstractNotification
{
    const ID = \Coyote\Notification::POST_COMMENT;

    /**
     * @var Comment
     */
    protected $comment;

    /**
     * @param Comment $comment
     */
    public function __construct(Comment $comment)
    {
        parent::__construct($comment->user, $comment->post);

        $this->comment = $comment;
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
            'subject'       => $this->post->topic->subject,
            'excerpt'       => excerpt($this->comment->html),
            'url'           => UrlBuilder::postComment($this->comment),
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
        return (new MailMessage)
            ->subject($this->getMailSubject())
            ->view($this->getMailView(), [
                'sender'    => $this->notifier->name,
                'subject'   => link_to($this->notificationUrl(), $this->post->topic->subject),
                'text'      => $this->comment->html
            ]);
    }

    /**
     * @return string
     */
    protected function getMailSubject(): string
    {
        return $this->notifier->name . ' dodał(a) komentarz w wątku: ' . $this->post->topic->subject;
    }

    /**
     * @return string
     */
    protected function getMailView(): string
    {
        return 'emails.notifications.post.comment';
    }
}
