<?php

namespace Coyote\Notifications\Post;

use Coyote\Services\UrlBuilder\UrlBuilder;
use Illuminate\Notifications\Messages\MailMessage;

class DeletedNotification extends AbstractNotification
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
        return $this->reasonName ?: '(moderator nie podał powodu)';
    }

    /**
     * @param string $reasonName
     */
    public function setReasonName($reasonName)
    {
        $this->reasonName = $reasonName;
    }

    /**
     * @return string
     */
    public function getReasonText()
    {
        return $this->reasonText ?: '(moderator nie podał powodu)';
    }

    /**
     * @param string $reasonText
     */
    public function setReasonText($reasonText)
    {
        $this->reasonText = $reasonText;
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
            ->view('emails.notifications.post.delete', [
                'sender'        => $this->notifier->name,
                'subject'       => link_to(UrlBuilder::post($this->post), $this->post->topic->subject),
                'reason_name'   => $this->reasonName,
                'reason_text'   => $this->reasonText
            ]);
    }

    /**
     * @return string
     */
    protected function getMailSubject(): string
    {
        return 'Post został usunięty przez ' . $this->notifier->name;
    }
}
