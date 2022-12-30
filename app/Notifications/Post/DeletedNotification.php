<?php

namespace Coyote\Notifications\Post;

use Coyote\Services\UrlBuilder;
use Coyote\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class DeletedNotification extends AbstractNotification implements ShouldQueue
{
    const ID = \Coyote\Notification::POST_DELETE;

    /**
     * @var string
     */
    private $reasonName;

    /**
     * @var string
     */
    private $reasonText;

    /**
     * @return string
     */
    public function getReasonName()
    {
        return $this->reasonName ?: '(nie podano powodu)';
    }

    /**
     * @param string|null $reasonName
     * @return $this
     */
    public function setReasonName(?string $reasonName)
    {
        $this->reasonName = $reasonName;

        return $this;
    }

    /**
     * @return string
     */
    public function getReasonText()
    {
        return $this->reasonText ?: '(nie podano powodu)';
    }

    /**
     * @param string|null $reasonText
     * @return $this
     */
    public function setReasonText(?string $reasonText)
    {
        $this->reasonText = $reasonText;

        return $this;
    }

    /**
     * @param User $user
     * @return array
     */
    public function toDatabase($user)
    {
        return [
            'object_id'     => $this->objectId(),
            'user_id'       => $user->id,
            'type_id'       => static::ID,
            'subject'       => $this->post->topic->title,
            'excerpt'       => $this->getReasonName(),
            'url'           => UrlBuilder::post($this->post),
            'id'            => $this->id
        ];
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
            ->line(
                sprintf(
                    '<strong>%s</strong> usunał post w wątku: <strong>%s</strong> z powodu: %s.',
                    $this->notifier->name,
                    htmlentities($this->post->topic->title),
                    $this->getReasonName()
                )
            )
            ->line('Szczegóły:')
            ->line('<hr>')
            ->line($this->getReasonText())
            ->line('<hr>')
            ->line('Otrzymujesz tego e-maila ponieważ obserwowałeś ten post lub jesteś jego autorem.');
    }

    /**
     * @return string
     */
    protected function getMailSubject(): string
    {
        return 'Post został usunięty przez ' . $this->notifier->name;
    }
}
