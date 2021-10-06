<?php

namespace Coyote\Notifications\Wiki;

use Coyote\Services\Notification\Notification;
use Coyote\Services\UrlBuilder;
use Coyote\User;
use Coyote\Wiki;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\WebPush\WebPushMessage;

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
            ->subject($this->getMailSubject())
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
            'headline'  => $this->getMailSubject(),
            'subject'   => $this->wiki->title,
            'url'       => $this->notificationUrl()
        ]);
    }

    public function toWebPush(): WebPushMessage
    {
        return (new WebPushMessage())
            ->title($this->getMailSubject())
            ->icon(url('/apple-touch.png'))
            ->body($this->wiki->title)
            ->tag($this->notificationUrl())
            ->data(['url' => $this->notificationUrl()])
            ->options(['TTL' => 1000]);
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

    protected function notificationUrl(): string
    {
        return route('user.notifications.redirect', ['path' => urlencode(UrlBuilder::wiki($this->wiki))]);
    }

    protected function getMailSubject(): string
    {
        return sprintf('%s zmodyfikował stronę %s', $this->log->user->name, $this->wiki->title);
    }
}
