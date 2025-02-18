<?php
namespace Coyote\Notifications\Job;

use Coyote\Job;
use Coyote\Services\Invoice\Calculator;
use Coyote\Services\Invoice\CalculatorFactory;
use Coyote\Services\Notification\DatabaseChannel;
use Coyote\Services\Notification\Notification;
use Coyote\Services\UrlBuilder;
use Coyote\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    const ID = \Coyote\Notification::JOB_CREATE;

    private Calculator $calculator;

    public function __construct(private Job $job) {}

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
            'object_id' => $this->objectId(),
            'user_id'   => $user->id,
            'type_id'   => static::ID,
            'subject'   => $this->job->title,
            'excerpt'   => 'Ogłoszenie zostało zapisane i oczekuje na płatność',
            'url'       => UrlBuilder::job($this->job),
            'id'        => $this->id,
        ];
    }

    /**
     * Generowanie unikalnego ciagu znakow dla wpisu na mikro
     *
     * @return string
     */
    public function objectId(): string
    {
        return substr(md5(static::ID . $this->job->id), 16);
    }

    public function sender(): array
    {
        return [
            'user_id' => $this->job->user_id,
            'name'    => $this->job->user->name,
        ];
    }

    public function toMail(): MailMessage
    {
        $offerLink = link_to(UrlBuilder::job($this->job), htmlentities($this->job->title));
        $netPrice = $this->calculator->netPrice();
        return (new MailMessage)
            ->subject("Ogłoszenie \"{$this->job->title}\" zostało dodane i oczekuje na płatność")
            ->line("Ogłoszenie $offerLink zostało zapisane i czeka na dokończenie płatności w kwocie $netPrice zł.")
            ->line('Po opłaceniu będzie ono widoczne w serwisie <strong>4programmers.net</strong>')
            ->action('Opłać ogłoszenie', route('job.payment', [$this->job->getUnpaidPayment()]))
            ->line('Dziękujemy za skorzystanie z naszych usług!');
    }

    protected function redirectionUrl(): string
    {
        return route('user.notifications.redirect', ['path' => UrlBuilder::job($this->job)]);
    }
}
