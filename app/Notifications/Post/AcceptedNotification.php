<?php

namespace Coyote\Notifications\Post;

use Coyote\Post;
use Coyote\Services\Notification\Notification;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Coyote\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;

class AcceptedNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    const ID = \Coyote\Notification::POST_ACCEPT;

    /**
     * @var User
     */
    private $acceptor;

    /**
     * @var Post
     */
    private $post;

    /**
     * @param User $acceptor
     * @param Post $post
     */
    public function __construct(User $acceptor, Post $post)
    {
        $this->acceptor = $acceptor;
        $this->post = $post;
    }

    /**
     * @param \Coyote\User $user
     * @return array
     */
    public function via($user)
    {
        if (!$user->can('access', $this->post->forum)) {
            return [];
        }

        return parent::getChannels($user);
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
            'excerpt'       => excerpt($this->post->html),
            'url'           => UrlBuilder::post($this->post),
            'guid'          => $this->id
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
            ->line(sprintf('%s zaakceptował Twój post w wątku <b>%s</b>', $this->acceptor->name, $this->post->topic->subject))
            ->action('Zobacz post', url($this->notificationUrl()));
    }

    /**
     * @return BroadcastMessage
     */
    public function toBroadcast()
    {
        return new BroadcastMessage([
            'headline'  => $this->acceptor->name . ' zaakceptował Twój post',
            'subject'   => $this->post->topic->subject,
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
        return substr(md5(class_basename($this) . $this->post->topic->subject), 16);
    }

    /**
     * @return array
     */
    public function sender()
    {
        return [
            'sender_name' => $this->acceptor->name,
            'sender_id' => $this->acceptor->id
        ];
    }
}
