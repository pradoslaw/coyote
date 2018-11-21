<?php

namespace Coyote\Notifications;

use Coyote\User;
use Coyote\Pm;
use Coyote\Services\Notification\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;

class PmCreatedNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    const ID = \Coyote\Notification::PM;

    /**
     * @var Pm
     */
    private $pm;

    /**
     * @var string
     */
    private $text;

    /**
     * @param Pm $pm
     */
    public function __construct(Pm $pm)
    {
        $this->pm = $pm;
        $this->text = app('parser.pm')->parse($this->pm->text->text);
    }

    /**
     * Get the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail()
    {
        return (new MailMessage)
            ->subject(sprintf('Masz nową wiadomość od: %s', $this->pm->user->name))
            ->view('emails.notifications.pm', [
                'text' => $this->text,
                'sender' => $this->pm->user->name,
                'url' => route('user.pm.show', [$this->pm->id - 1], false)
            ]
        );
    }

    /**
     * @param User $user
     * @return array
     */
    public function toDatabase(User $user)
    {
        $excerpt = excerpt($this->text);

        return [
            'object_id'     => $this->objectId(),
            'user_id'       => $user->id,
            'type_id'       => static::ID,
            'subject'       => $excerpt,
            'excerpt'       => $excerpt,
            'url'           => route('user.pm.show', [$this->pm->id - 1], false),
            'guid'          => $this->id
        ];
    }

    /**
     * @return BroadcastMessage
     */
    public function toBroadcast()
    {
        return new BroadcastMessage([
            'headline'  => $this->pm->user->name . ' przesyła Ci nową wiadomość',
            'subject'   => excerpt($this->text),
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
        return substr(md5(class_basename($this) . $this->pm->user_id), 16);
    }

    /**
     * @return array
     */
    public function sender()
    {
        return [
            'user_id'       => $this->pm->user_id,
            'name'          => $this->pm->user->name
        ];
    }
}
