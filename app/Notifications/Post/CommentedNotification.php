<?php

namespace Coyote\Notifications\Post;

use Coyote\Post\Comment;
use Coyote\Services\UrlBuilder;
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
            'subject'       => $this->post->topic->title,
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
            ->line(
                sprintf(
                    '<strong>%s</strong> dodał komentarz do posta w wątku: <strong>%s</strong>',
                    $this->notifier->name,
                    $this->post->topic->title
                )
            )
            ->line('<hr>')
            ->line($this->comment->html)
            ->line('<hr>')
            ->action('Zobacz komentarz', url($this->notificationUrl()))
            ->line('Jeżeli nie chcesz dostawać tego typu powiadomień, kliknij na przycisk <i>Obserwuj</i> pod postem, aby przestać obserwować dany post.');
    }

    /**
     * @return string
     */
    protected function getMailSubject(): string
    {
        return $this->notifier->name . ' dodał(a) komentarz w wątku: ' . $this->post->topic->title;
    }
}
