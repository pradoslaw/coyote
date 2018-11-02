<?php

namespace Coyote\Notifications;

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
     * @param \Coyote\User $user
     * @return array
     */
    public function via($user)
    {
        return parent::getChannels($user);
    }

    /**
     * Get the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail()
    {
        return (new MailMessage)
            ->subject(sprintf('Masz nową wiadomość od: %s', $this->pm->author->name))
            ->view('emails.notifications.pm', [
                'text' => $this->text,
                'sender' => $this->pm->author->name
            ]
        );
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
            'subject'       => excerpt($this->text),
            'excerpt'       => $this->text,
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
            'headline'  => $this->pm->author->name . ' przesyła Ci nową wiadomość',
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
        return substr(md5(class_basename($this) . $this->pm->author_id), 16);
    }

    /**
     * @return array
     */
    public function sender()
    {
        return [
            'user_id'       => $this->pm->author_id,
            'name'          => $this->pm->author->name
        ];
    }
}
