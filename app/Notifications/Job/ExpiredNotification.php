<?php

namespace Coyote\Notifications\Job;

use Coyote\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ExpiredNotification extends Notification
{
    use Queueable;

    /**
     * @var Job
     */
    private $job;

    /**
     * @param Job $job
     */
    public function __construct(Job $job)
    {
        $this->job = $job;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return string[]
     */
    public function via()
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail()
    {
        $message = (new MailMessage)
            ->subject(sprintf('Twoje ogłoszenie "%s" wygasło.', $this->job->title))
            ->line(
                sprintf(
                    'Twoje ogłoszenie <b>%s</b> zostało zakończone i nie jest już wyświetlane w serwisie <b>%s<b>',
                    link_to_route('job.offer', $this->job->title, [$this->job->id, $this->job->slug]),
                    config('app.name')
                )
            );

        if ($this->job->enable_apply) {
            $message->line(sprintf('Liczba wysłanych aplikacji: <b>%s</b>', $this->job->applications->count()));
        }

        return $message
            ->action('Wystaw ponownie', route('job.renew', [$this->job]))
            ->line('Dziękujemy za skorzystanie z naszego serwisu. W razie dodatkowych pytań pozostajemy do dyspozycji.');
    }
}
