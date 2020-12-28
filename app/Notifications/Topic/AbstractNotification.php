<?php

namespace Coyote\Notifications\Topic;

use Coyote\Services\Notification\Notification;
use Coyote\Services\UrlBuilder;
use Coyote\Topic;
use Coyote\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;

abstract class AbstractNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    /**
     * @var Topic
     */
    protected $topic;

    /**
     * @var User|null
     */
    protected $notifier;

    /**
     * @var string
     */
    protected $reasonName;

    /**
     * @var string
     */
    protected $reasonText;

    /**
     * @param User|null $notifier
     * @param Topic $topic
     */
    public function __construct(?User $notifier, Topic $topic)
    {
        $this->notifier = $notifier;
        $this->topic = $topic;
    }

    /**
     * @return string
     */
    public function getReasonName(): string
    {
        return $this->reasonName ?: '(nie podano powodu)';
    }

    /**
     * @param string|null $reasonName
     * @return $this
     */
    public function setReasonName(?string $reasonName)
    {
        $this->reasonName = $reasonName;

        return $this;
    }

    /**
     * @return string
     */
    public function getReasonText(): string
    {
        return $this->reasonText ?: '(nie podano powodu)';
    }

    /**
     * @param string|null $reasonText
     * @return $this
     */
    public function setReasonText(?string $reasonText)
    {
        $this->reasonText = $reasonText;

        return $this;
    }

    /**
     * Unikalne ID okreslajace dano powiadomienie. To ID posluzy do grupowania powiadomien tego samego typu
     *
     * @return string
     */
    public function objectId()
    {
        return substr(md5(class_basename($this) . $this->topic->title . $this->topic->id), 16);
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
        if (!$user->can('access', $this->topic->forum)) {
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
            'subject'       => $this->topic->title,
            'excerpt'       => $this->getReasonName(),
            'url'           => UrlBuilder::topic($this->topic),
            'id'            => $this->id
        ];
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
            'subject'   => $this->topic->title,
            'url'       => $this->notificationUrl()
        ]);
    }
}
