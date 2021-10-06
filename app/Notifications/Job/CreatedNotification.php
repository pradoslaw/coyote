<?php

namespace Coyote\Notifications\Job;

use Coyote\Job;
use Coyote\Services\Invoice\Calculator;
use Coyote\Services\Invoice\CalculatorFactory;
use Coyote\Services\Notification\Notification;
use Coyote\Services\UrlBuilder;
use Coyote\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Coyote\Services\Notification\DatabaseChannel;

class CreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    const ID = \Coyote\Notification::JOB_CREATE;

    /**
     * @var Job
     */
    private $job;

    /**
     * @var Calculator
     */
    private $calculator;

    /**
     * @param Job $job
     */
    public function __construct(Job $job)
    {
        $this->job = $job;
    }

    /**
     * @param User $user
     * @return array
     */
    public function via(User $user)
    {
        $payment = $this->job->getUnpaidPayment();

        if (!$payment) {
            return [];
        }

        // calculate price based on payment details
        $this->calculator = CalculatorFactory::payment($payment);

        if (!$this->calculator->grossPrice()) {
            return [];
        }

        return ['mail', DatabaseChannel::class];
    }

    /**
     * @param \Coyote\User $user
     * @return array
     */
    public function toDatabase($user)
    {
        return [
            'object_id'     => $this->objectId(),
            'user_id'       => $user->id,
            'type_id'       => static::ID,
            'subject'       => $this->job->title,
            'excerpt'       => 'Ogłoszenie zostało dodane i oczekuje na płatność',
            'url'           => UrlBuilder::job($this->job),
            'id'            => $this->id
        ];
    }

    /**
     * Generowanie unikalnego ciagu znakow dla wpisu na mikro
     *
     * @return string
     */
    public function objectId()
    {
        return substr(md5(static::ID . $this->job->id), 16);
    }

    /**
     * @return array
     */
    public function sender()
    {
        return [
            'user_id'       => $this->job->user_id,
            'name'          => $this->job->user->name
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
            ->subject(sprintf('Ogłoszenie "%s" zostało dodane i oczekuje na płatność', $this->job->title))
            ->line(sprintf('Dziękujemy za dodanie ogłoszenia w serwisie <strong>%s</strong>.', config('app.name')))
            ->line(
                sprintf(
                    'Ogłoszenie %s zostało dodane i czeka dokonanie opłaty w kwocie %s zł.',
                    link_to(UrlBuilder::job($this->job), $this->job->title),
                    $this->calculator->netPrice()
                )
            )
            ->action('Opłać ogłoszenie', route('job.payment', [$this->job->getUnpaidPayment()]))
            ->line('Dziękujemy za skorzystanie z naszych usług!');
    }

    protected function redirectionUrl(): string
    {
        return route('user.notifications.redirect', ['path' => UrlBuilder::job($this->job)]);
    }
}
