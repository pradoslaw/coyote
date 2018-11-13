<?php

namespace Coyote\Notifications\Microblog;

use Coyote\Services\UrlBuilder\UrlBuilder;
use Coyote\User;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;

class SubmittedNotification extends AbstractNotification
{
    const ID = \Coyote\Notification::MICROBLOG_SUBSCRIBER;

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
     * Get the mail representation of the notification.
     *
     * @param \Coyote\User $user
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($user)
    {
        return (new MailMessage)
            ->subject($user->name . ' dodał komentarz do wpisu na mikroblogu')
            ->line(
                sprintf(
                    '<strong>%s</strong> dodał nowy komentarz we wpisie na mikroblogu: <strong>%s</strong>',
                    $user->name,
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
