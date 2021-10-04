<?php

namespace Coyote\Notifications\Microblog;

use Coyote\Microblog;
use Coyote\Services\Notification\Notification;
use Coyote\Services\UrlBuilder;
use Coyote\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use NotificationChannels\WebPush\WebPushMessage;

abstract class AbstractNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    /**
     * Indicate if the job should be deleted when models are missing.
     *
     * @var bool
     */
    public $deleteWhenMissingModels = true;

    /**
     * @var Microblog
     */
    protected $microblog;

    /**
     * @var User
     */
    public $notifier;

    /**
     * @var string
     */
    protected $url;

    /**
     * @param Microblog $microblog
     */
    public function __construct(Microblog $microblog)
    {
        $this->microblog = $microblog;
        $this->notifier = $this->microblog->user;
    }

    /**
     * @return array
     */
    public function sender()
    {
        return [
            'user_id'       => $this->notifier->id,
            'name'          => $this->notifier->name
        ];
    }

    /**
     * Generowanie unikalnego ciagu znakow dla wpisu na mikro
     *
     * @return string
     */
    public function objectId()
    {
        return substr(md5(class_basename($this) . ($this->microblog->parent_id ?: $this->microblog->id)), 16);
    }

    /**
     * @return BroadcastMessage
     */
    public function toBroadcast(): BroadcastMessage
    {
        return new BroadcastMessage([
            'headline'  => $this->getMailSubject(),
            'subject'   => excerpt($this->microblog->html),
            'url'       => $this->notificationUrl(),
        ]);
    }

    public function toWebPush(): WebPushMessage
    {
        return (new WebPushMessage())
            ->title($this->getMailSubject())
            ->icon(url('/apple-touch.png'))
            ->body(excerpt($this->microblog->html))
            ->tag($this->notificationUrl())
            ->data(['url' => $this->notificationUrl()])
            ->options(['TTL' => 1000]);
    }

    protected function microblogUrl(): string
    {
        return $this->microblog->parent_id ? UrlBuilder::microblogComment($this->microblog) : UrlBuilder::microblog($this->microblog);
    }

    protected function notificationUrl(): string
    {
        return route('user.notifications.redirect', ['path' => urlencode($this->microblogUrl())]);
    }

    /**
     * @return string
     */
    abstract protected function getMailSubject(): string;
}
