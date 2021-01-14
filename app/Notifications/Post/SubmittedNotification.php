<?php

namespace Coyote\Notifications\Post;

use Illuminate\Notifications\Messages\MailMessage;

class SubmittedNotification extends AbstractNotification
{
    const ID = \Coyote\Notification::TOPIC_SUBSCRIBER;

    /**
     * @var string
     */
    protected $sender;

    /**
     * Get the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail()
    {
        return (new MailMessage)
            ->subject($this->getMailSubject())
            ->line(
                sprintf(
                    '<strong>%s</strong> dodał nowy post w wątku: <strong>%s</strong>',
                    $this->getSender(),
                    $this->post->topic->title
                )
            )
            ->line('<hr>')
            ->line($this->post->html)
            ->line('<hr>')
            ->action('Zobacz post', url($this->notificationUrl()));
    }

    /**
     * @param string|null $sender
     * @return $this
     */
    public function setSender(?string $sender)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * @return string
     */
    protected function getSender(): string
    {
        return $this->sender ?: $this->notifier->name;
    }

    /**
     * @return array
     */
    public function sender()
    {
        return [
            'name' => $this->getSender(),
            'user_id' => $this->notifier->id ?? null
        ];
    }

    /**
     * Unikalne ID okreslajace dano powiadomienie. To ID posluzy do grupowania powiadomien tego samego typu
     *
     * @return string
     */
    public function objectId()
    {
        return substr(md5(class_basename($this) . $this->post->topic->id), 16);
    }

    /**
     * @return string
     */
    protected function getMailSubject(): string
    {
        return $this->getSender() . ' napisał post w wątku: ' . $this->post->topic->title;
    }
}
