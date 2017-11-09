<?php

namespace Coyote\Notifications;

use Coyote\Microblog;
use Coyote\Services\Notification\Notification;
use Coyote\Services\Notification\UserNotificationInterface;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Coyote\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;

class MicroblogCommentNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    const ID = \Coyote\Notification::MICROBLOG;

    /**
     * @var Microblog
     */
    private $microblog;

    /**
     * @var string
     */
    private $url;

    /**
     * @param Microblog $microblog
     */
    public function __construct(Microblog $microblog)
    {
        $this->microblog = $microblog;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  User  $user
     * @return array
     */
    public function via($user)
    {
        $this->url = UrlBuilder::microblogComment($this->microblog->parent, $this->microblog->id);

        return parent::getChannels($user);
    }

    /**
     * @param \Coyote\User $user
     * @return array
     */
    public function toDatabase($user)
    {
        return [
            'object_id'     => $this->objectId(),
            'user_id'       => $user->id,
            'type_id'       => static::ID,
            'subject'       => excerpt($this->microblog->parent->html),  // original excerpt of parent entry
            'excerpt'       => excerpt($this->microblog->html),
            'url'           => $this->url,
            'guid'          => $this->id
        ];
    }

    /**
     * @return array
     */
    public function sender()
    {
        return [
            'user_id'       => $this->microblog->user_id,
            'name'          => $this->microblog->user->name
        ];
    }

    /**
     * Generowanie unikalnego ciagu znakow dla wpisu na mikro
     *
     * @return string
     */
    public function objectId()
    {
        return substr(md5(class_basename($this) . $this->microblog->parent_id), 16);
    }

    /**
     * Get the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail()
    {
        return (new MailMessage)
            ->line(
                sprintf(
                    '<strong>%s</strong> dodał nowy komentarz we wpisie na mikroblogu: <strong>%s</strong>',
                    $this->microblog->user->name,
                    excerpt($this->microblog->parent->html)
                )
            )
            ->action('Zobacz komentarz', url($this->url))
            ->line('Dostajesz to powiadomienie, ponieważ obserwujesz ten wpis.');
    }

    /**
     * @param \Coyote\User $user
     * @return BroadcastMessage
     */
    public function toBroadcast($user)
    {
        return new BroadcastMessage([
            'headline'  => $user->name . ' dodał komentarz do wpisu na mikroblogu',
            'subject'   => excerpt($this->microblog->html),
            'url'       => $this->notificationUrl()
        ]);
    }
}
