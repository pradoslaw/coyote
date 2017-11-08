<?php

namespace Coyote\Notifications;

use Coyote\Microblog;
use Coyote\Notification\Setting;
use Coyote\Services\Notification\DatabaseChannel;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Coyote\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class MicroblogCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    const ID = \Coyote\Notification::MICROBLOG;

    /**
     * @var Microblog
     */
    private $microblog;

    protected $subject;
    protected $excerpt;
    protected $url;

    protected $senderName;
    protected $senderId;

    /**
     * @param Microblog $microblog
     */
    public function __construct(Microblog $microblog)
    {
        $this->microblog = $microblog;

        $this->subject = excerpt($this->microblog->parent->html);  // original excerpt of parent entry
        $this->excerpt = excerpt($this->microblog->html);
        $this->url = UrlBuilder::microblogComment($this->microblog->parent, $this->microblog->id);
        $this->senderId = $this->microblog->user_id;
        $this->senderName = $this->microblog->user->name;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  User  $user
     * @return array
     */
    public function via($user)
    {
        return $this->notificationChannels($user);
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
            'subject'       => $this->subject,
            'excerpt'       => $this->excerpt,
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
            'user_id'       => $this->senderId,
            'name'          => $this->senderName
        ];
    }

    /**
     * Generowanie unikalnego ciagu znakow dla wpisu na mikro
     *
     * @return string
     */
    public function objectId()
    {
        return substr(md5(static::ID . $this->microblog->parent_id), 16);
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line(sprintf('<strong>%s</strong> dodał nowy komentarz we wpisie na mikroblogu: <strong>%s</strong>', $this->senderName, $this->subject))
                    ->action('Zobacz komentarz', url($this->url))
                    ->line('Dostajesz to powiadomienie, ponieważ obserwujesz ten wpis.');
    }

    public function notificationChannels(User $user)
    {
        $channels = [];
        $settings = $user->hasOne(Setting::class)->where('type_id', static::ID)->first();

        if ($settings->profile) {
            $channels[] = DatabaseChannel::class;
        }

        if ($user->email && $user->is_active && $user->is_confirm && !$user->is_blocked && $settings->email) {
            $channels[] = 'mail';
        }

        return $channels;
    }
}
