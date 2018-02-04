<?php

namespace Coyote\Notifications;

use Coyote\Job\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ApplicationConfirmationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via()
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  Application  $application
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($application)
    {
        return (new MailMessage)
            ->greeting($application->name)
            ->line(
                sprintf(
                    'Dziękujemy za udział w rekrutacji na stanowisko %s.',
                    link_to_route('job.offer', $application->job->title)
                )
            )
            ->line(
                sprintf(
                    'Twoja aplikacja została przekazana do osoby odpowiedzialnej za proces rekrutacyjny. Możesz się z nią skontaktować pod adresem: %s',
                    $application->job->email
                )
            );
    }
}
