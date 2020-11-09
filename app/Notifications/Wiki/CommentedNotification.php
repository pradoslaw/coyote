<?php

namespace Coyote\Notifications\Wiki;

use Coyote\Services\Notification\Notification;
use Coyote\Services\UrlBuilder;
use Coyote\User;
use Coyote\Wiki\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;

class CommentedNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    const ID = \Coyote\Notification::WIKI_COMMENT;

    /**
     * @var Comment
     */
    private $comment;

    /**
     * CommentedNotification constructor.
     * @param Comment $comment
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail()
    {
        return (new MailMessage())
            ->subject(sprintf('%s dodał komentarz do strony %s', $this->comment->user->name, $this->comment->wiki->title))
            ->line(sprintf('%s dodał komentarz do strony, którą obserwujesz', $this->comment->user->name))
            ->action('Zobacz komentarz', $this->notificationUrl());
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
            'subject'       => $this->comment->wiki->title,
            'excerpt'       => excerpt($this->comment->html),
            'url'           => UrlBuilder::wikiComment($this->comment->wiki, $this->comment->id),
            'id'            => $this->id
        ];
    }

    /**
     * @return BroadcastMessage
     */
    public function toBroadcast()
    {
        return new BroadcastMessage([
            'headline'  => "$this->comment->user->name dodał komentarz",
            'subject'   => $this->comment->wiki->title,
            'url'       => $this->notificationUrl()
        ]);
    }

    /**
     * Unikalne ID okreslajace dano powiadomienie. To ID posluzy do grupowania powiadomien tego samego typu
     *
     * @return string
     */
    public function objectId()
    {
        return substr(md5(class_basename($this) . $this->comment->wiki->id), 16);
    }

    /**
     * @return array
     */
    public function sender()
    {
        return [
            'user_id'       => $this->comment->user_id,
            'name'          => $this->comment->user->name
        ];
    }
}
