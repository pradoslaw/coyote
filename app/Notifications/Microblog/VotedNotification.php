<?php

namespace Coyote\Notifications\Microblog;

use Coyote\Microblog\Vote;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Coyote\User;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;

class VotedNotification extends AbstractNotification implements ShouldQueue, ShouldBroadcast
{
    const ID = \Coyote\Notification::MICROBLOG_VOTE;

    /**
     * @var Vote
     */
    private $vote;

    /**
     * @param Vote $vote
     */
    public function __construct(Vote $vote)
    {
        $this->vote = $vote;
        $this->microblog = $vote->microblog;
    }

    /**
     * @return array
     */
    public function sender()
    {
        return [
            'user_id'       => $this->vote->user_id,
            'name'          => $this->vote->user->name
        ];
    }

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
            ->subject($this->vote->user->name . ' docenił Twój wpis na mikroblogu')
            ->line(
                sprintf(
                    'Informujemy, ze <strong>%s</strong> docenił Twój wpis na mikroblogu.',
                    $this->vote->user->name
                )
            )
            ->action('Zobacz wpis', url($this->notificationUrl()))
            ->line('Dostajesz to powiadomienie, ponieważ obserwujesz ten wpis.');
    }

    /**
     * @return BroadcastMessage
     */
    public function toBroadcast()
    {
        return new BroadcastMessage([
            'headline'  => $this->vote->user->name . ' docenił Twój wpis na mikroblogu',
            'subject'   => excerpt($this->microblog->html),
            'url'       => $this->notificationUrl()
        ]);
    }
}
