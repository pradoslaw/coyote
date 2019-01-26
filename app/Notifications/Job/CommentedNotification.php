<?php

namespace Coyote\Notifications\Job;

use Coyote\Job\Comment;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommentedNotification extends Notification implements ShouldQueue
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
            ->subject(sprintf('Nowy komentarz do Twojego ogłoszenia %s.', $this->comment->job->title))
            ->line(
                sprintf(
                    'Do Twojego ogłoszenia <b>%s</b> dodany został nowy komentarz.',
                    link_to(UrlBuilder::job($this->comment->job), $this->comment->job->title)
                )
            )
            ->action(
                'Kliknij, aby go zobaczyć i odpowiedzieć',
                UrlBuilder::job($this->comment->job) . '#comment-' . $this->comment->id
            );
    }
}
