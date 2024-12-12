<?php

namespace Coyote\Notifications\Post;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SubmittedNotification extends AbstractNotification implements ShouldQueue
{
    const ID = \Coyote\Notification::TOPIC_SUBSCRIBER;

    /**
     * @var string
     */
    protected $sender;

    public function toMail()
    {
        return (new MailMessage)
            ->subject($this->getMailSubject())
            ->line(
                \sPrintF(
                    '<strong>%s</strong> dodał nowy post w wątku: <strong>%s</strong>',
                    $this->getSender(),
                    htmlentities($this->post->topic->title),
                ),
            )
            ->line('<hr>')
            ->line($this->post->html)
            ->line('<hr>')
            ->action('Zobacz post', url($this->redirectionUrl()));
    }

    protected function getSender(): string
    {
        return $this->sender ?: $this->notifier->name;
    }

    public function sender(): array
    {
        return [
            'name'    => $this->getSender(),
            'user_id' => $this->notifier->id ?? null,
        ];
    }

    /**
     * Unikalne ID okreslajace dane powiadomienie. To ID posluzy do grupowania powiadomien tego samego typu
     */
    public function objectId(): string
    {
        return substr(md5(class_basename($this) . $this->post->topic->id), 16);
    }

    protected function getMailSubject(): string
    {
        return $this->getSender() . ' napisał post w wątku: ' . $this->post->topic->title;
    }
}
