<?php

namespace Coyote\Notifications\Job;

use Coyote\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class ApplicationSentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var Job\Application
     */
    private $application;

    /**
     * @param Job\Application $application
     */
    public function __construct(Job\Application $application)
    {
        $this->application = $application;
    }

    /**
     * @param Job $job
     * @return array
     */
    public function via(Job $job)
    {
        $channels = ['mail'];

        if (!empty($job->phone)) {
            $channels[] = TwilioChannel::class;
        }

        return $channels;
    }

    /**
     * @param Job $job
     * @return TwilioSmsMessage
     */
    public function toTwilio(Job $job)
    {
        return (new TwilioSmsMessage())
            ->content(
                sprintf(
                    '%s wysłał swoją aplikację w odpowiedzi na ogłoszenie "%s". Pozdrawiamy, %s',
                    $this->application->name,
                    $job->title,
                    config('app.name')
                )
            );
    }

    /**
     * @param Job $job
     * @return MailMessage
     */
    public function toMail(Job $job)
    {
        $message = (new MailMessage())
            ->subject(sprintf('[%s] %s', $this->application->name, $job->title))
            ->replyTo($this->application->email, $this->application->name)
            ->view('emails.job.application', [
                'application' => $this->application->toArray(),
                'job' => $job
            ]);

        if ($this->application->cv) {
            $path = realpath(storage_path('app/cv/' . $this->application->cv));
            $filename = basename($path);

            $name = explode('_', $filename, 2)[1];
            $message->attach($path, ['as' => $name]);
        }

        return $message;
    }
}
