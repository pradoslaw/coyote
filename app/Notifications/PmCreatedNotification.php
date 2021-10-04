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
use NotificationChannels\WebPush\WebPushMessage;

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
            ->subject($this->getMailSubject())
            ->view(
                'emails.notifications.pm',
                [
                    'text' => $this->text,
                    'sender' => $this->pm->author->name,
                    'url' => route('user.pm.show', [$this->pm->id], false)
                ]
            );
    }

    /**
     * @param User $user
     * @return array
     */
    public function toDatabase($user)
    {
        $excerpt = excerpt($this->text);

        return [
            'object_id'     => $this->objectId(),
            'user_id'       => $user->id,
            'type_id'       => static::ID,
            'subject'       => $excerpt,
            'excerpt'       => $excerpt,
            'url'           => route('user.pm.show', [$this->pm->id], false),
            'id'            => $this->id,
            'content_id'    => $this->pm->id,
            'content_type'  => class_basename($this->pm)
        ];
    }

    /**
     * @return BroadcastMessage
     */
    public function toBroadcast()
    {
        return new BroadcastMessage([
            'headline'  => $this->getMailSubject(),
            'subject'   => excerpt($this->text),
            'url'       => $this->notificationUrl()
        ]);
    }

    public function toWebPush(): WebPushMessage
    {
        return (new WebPushMessage())
            ->title($this->getMailSubject())
            ->icon(url('/apple-touch.png'))
            ->body(excerpt($this->text))
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
        return substr(md5(class_basename($this) . $this->pm->user_id), 16);
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

    private function getMailSubject(): string
    {
        return sprintf('Masz nową wiadomość od: %s', $this->pm->author->name);
    }
}
