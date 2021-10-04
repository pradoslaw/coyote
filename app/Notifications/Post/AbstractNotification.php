<?php

namespace Coyote\Notifications\Post;

use Coyote\Post;
use Coyote\Services\Notification\Notification;
use Coyote\Services\UrlBuilder;
use Coyote\Topic;
use Coyote\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use NotificationChannels\WebPush\WebPushMessage;

abstract class AbstractNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    /**
     * @var User|null
     */
    protected $notifier;

    /**
     * @var Post
     */
    protected $post;

    /**
     * @param User|null $notifier
     * @param Post $post
     */
    public function __construct(?User $notifier, Post $post)
    {
        $this->notifier = $notifier;
        $this->post = $post;
    }

    /**
     * @return string
     */
    abstract protected function getMailSubject(): string;

    /**
     * @param User $user
     * @return array
     */
    public function via(User $user)
    {
        if (!$user->can('access', $this->post->forum)) {
            return [];
        }

        return parent::channels($user);
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
            'excerpt'       => excerpt($this->post->html),
            'url'           => UrlBuilder::post($this->post),
            'id'            => $this->id,
            'content_type'  => Topic::class,
            'content_id'    => $this->post->topic_id
        ];
    }

    /**
     * Unikalne ID okreslajace dano powiadomienie. To ID posluzy do grupowania powiadomien tego samego typu
     *
     * @return string
     */
    public function objectId()
    {
        return substr(md5(class_basename($this) . $this->post->id), 16);
    }

    /**
     * @return array
     */
    public function sender()
    {
        return [
            'name' => $this->notifier->name,
            'user_id' => $this->notifier->id
        ];
    }

    /**
     * @return BroadcastMessage
     */
    public function toBroadcast()
    {
        return new BroadcastMessage([
            'headline'  => $this->getMailSubject(),
            'subject'   => excerpt($this->post->html),
            'url'       => $this->notificationUrl()
        ]);
    }

    public function toWebPush()
    {
        return (new WebPushMessage())
            ->title($this->getMailSubject())
            ->icon('/apple-touch.png')
            ->body(excerpt($this->post->html))
            ->data(['url' => $this->notificationUrl()])
            ->options(['TTL' => 1000]);
    }
}
