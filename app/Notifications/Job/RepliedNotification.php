<?php

namespace Coyote\Notifications\Job;

use Coyote\Job\Comment;
use Coyote\Services\UrlBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RepliedNotification extends Notification
{
    use Queueable;

    /**
     * @var Comment
     */
    private $comment;

    /**
     * @param Comment $comment
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return array
     */
    public function via()
    {
        return ['mail'];
    }

    /**
     * @return MailMessage
     */
    public function toMail()
    {
        return (new MailMessage())
            ->subject('Odpowiedź do Twojego komentarza w ogłoszeniu ' . $this->comment->job->title)
            ->line(
                sprintf(
                    'Udzielono odpowiedzi na Twój komentarz do ogłoszenia <strong>%s</strong>.',
                    link_to(UrlBuilder::job($this->comment->job), $this->comment->job->title)
                )
            )
            ->action('Kliknij, aby ją zobaczyć', UrlBuilder::jobComment($this->comment->job, $this->comment->id));
    }
}
