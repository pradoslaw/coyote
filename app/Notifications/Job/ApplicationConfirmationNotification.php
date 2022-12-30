<?php

namespace Coyote\Notifications\Job;

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
            ->subject(sprintf('Potwierdzenie udziału w rekrutacji na stanowisko %s', htmlentities($application->job->title)))
            ->line(
                sprintf(
                    'Dziękujemy za udział w rekrutacji na stanowisko <b>%s</b>.',
                    link_to_route('job.offer', htmlentities($application->job->title), [$application->job->id, $application->job->slug])
                )
            )
            ->line(
                sprintf(
                    'Twoja aplikacja została przekazana do osoby odpowiedzialnej za proces rekrutacyjny. Jeżeli chcesz, możesz się z nią skontaktować pod adresem: %s.',
                    link_to('mailto:' . $application->job->email, $application->job->email)
                )
            );
    }
}
