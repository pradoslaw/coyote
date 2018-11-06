<?php

namespace Coyote\Notifications\Microblog;

use Coyote\Services\UrlBuilder\UrlBuilder;
use Coyote\User;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;

class UserMentionedNotification extends AbstractNotification implements ShouldQueue, ShouldBroadcast
{
    const ID = \Coyote\Notification::MICROBLOG_LOGIN;

    /**
     * Get the notification's delivery channels.
     *
     * @param  User  $user
     * @return array
     */
    public function via(User $user)
    {
        $this->url = $this->microblog->parent_id
            ? UrlBuilder::microblogComment($this->microblog->parent, $this->microblog->id)
                : UrlBuilder::microblog($this->microblog);

        return parent::getChannels($user);
    }


    /**
     * @param User $user
     * @return array
     */
    public function toDatabase(User $user)
    {
        return [
            'object_id'     => $this->objectId(),
            'user_id'       => $user->id,
            'type_id'       => static::ID,
            'subject'       => excerpt($this->microblog->parent_id ? $this->microblog->parent->html : $this->microblog->html),  // original excerpt of parent entry
            'excerpt'       => excerpt($this->microblog->html),
            'url'           => $this->url,
            'guid'          => $this->id
        ];
    }

    /**
     * Generowanie unikalnego ciagu znakow dla wpisu na mikro
     *
     * @return string
     */
    public function objectId()
    {
        return substr(md5(class_basename($this) . $this->microblog->parent_id ?: $this->microblog->id), 16);
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param \Coyote\User $user
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($user)
    {
        return (new MailMessage())
            ->subject($user->name . ' wspomniał o Tobie na mikroblogu')
            ->line(
                sprintf(
                    '<strong>%s</strong> użył Twojego loginu w treści wpisu mikrobloga: <strong>%s</strong>',
                    $user->name,
                    excerpt($this->microblog->html)
                )
            )
            ->action('Zobacz', url($this->url))
            ->line('Dostajesz to powiadomienie, ponieważ wynika to z ustawień Twojego konta.');
    }

    /**
     * @param \Coyote\User $user
     * @return BroadcastMessage
     */
    public function toBroadcast($user)
    {
        return new BroadcastMessage([
            'headline'  => $user->name . ' wspomniał o Tobie na mikroblogu',
            'subject'   => excerpt($this->microblog->html),
            'url'       => $this->notificationUrl()
        ]);
    }
}
