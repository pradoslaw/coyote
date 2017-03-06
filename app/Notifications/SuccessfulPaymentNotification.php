<?php

namespace Coyote\Notifications;

use Coyote\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SuccessfulPaymentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var Payment
     */
    private $payment;

    /**
     * @var string
     */
    private $pdf;

    /**
     * @param Payment $payment
     * @param string $pdf
     */
    public function __construct(Payment $payment, string $pdf)
    {
        $this->payment = $payment;
        $this->pdf = $pdf;
    }

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
     * @param  \Coyote\User  $user
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($user)
    {
        return (new MailMessage)
            ->to($user->email)
            ->subject(sprintf('Potwierdzenie płatności za promowanie oferty: %s', $this->payment->job->title))
            ->line(
                sprintf(
                    'Otrzymaliśmy płatność w kwocie <strong>%s %s</strong>.',
                    $this->payment->invoice->grossPrice(),
                    $this->payment->invoice->currency->symbol
                )
            )
            ->line('W załączniku znajdziesz fakturę VAT.')
            ->line(
                sprintf(
                    'Twoje ogłoszenie <strong>%s</strong> jest już promowane w naszym serwisie.',
                    $this->payment->job->title
                )
            )
            ->action('Zobacz ogłoszenie', route('job.offer', [$this->payment->job->id, $this->payment->job->slug]))
            ->line('Dziekujemy za skorzystanie z naszych usług.')
            ->attachData(base64_decode($this->pdf), $this->getFilename());
    }

    /**
     * @return string
     */
    private function getFilename(): string
    {
        return str_replace('/', '_', $this->payment->invoice->number) . '.pdf';
    }
}
