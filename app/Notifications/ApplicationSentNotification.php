<?php

namespace Coyote\Notifications;

use Coyote\Job;
use Illuminate\Bus\Queueable;
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
        if (empty($job->phone)) {
            return [];
        }

        return [TwilioChannel::class];
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
}
