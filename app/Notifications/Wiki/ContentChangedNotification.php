<?php

namespace Coyote\Notifications\Wiki;

use Coyote\Services\Notification\Notification;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Coyote\User;
use Coyote\Wiki;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;

class ContentChangedNotification extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;

    const ID = \Coyote\Notification::WIKI_SUBSCRIBER;

    /**
     * @var Wiki
     */
    private $wiki;

    /**
     * @var Wiki\Log
     */
    private $log;

    /**
     * ContentChangedNotification constructor.
     * @param Wiki $wiki
     */
    public function __construct(Wiki $wiki)
    {
        $this->wiki = $wiki;
        $this->log = $wiki->logs()->orderBy('id', 'DESC')->limit(1)->get()->first();
    }

    /**
     * Get the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail()
    {
        return (new MailMessage())
            ->subject(sprintf('%s zmodyfikował stronę %s', $this->log->user->name, $this->wiki->title))
            ->line(sprintf('%s wprowadził zmiany na stronie %s, którą obserwujesz', $this->log->user->name, $this->wiki->title))
            ->action('Zobacz stronę', $this->notificationUrl());
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
            'subject'       => $this->wiki->title,
            'excerpt'       => excerpt($this->wiki->excerpt),
            'url'           => UrlBuilder::wiki($this->wiki),
            'id'            => $this->id
        ];
    }

    /**
     * @return BroadcastMessage
     */
    public function toBroadcast()
    {
        return new BroadcastMessage([
            'headline'  => "$this->wiki->user->name zmodyfikował stronę",
            'subject'   => $this->wiki->title,
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
        return substr(md5(class_basename($this) . $this->wiki->id), 16);
    }

    /**
     * @return array
     */
    public function sender()
    {
        return [
            'user_id'       => $this->log->user_id,
            'name'          => $this->log->user->name
        ];
    }
}
