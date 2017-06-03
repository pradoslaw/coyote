<?php

namespace Coyote\Notifications;

use Coyote\Payment;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SuccessfulPaymentNotification extends Notification
{
    /**
     * @var Payment
     */
    private $payment;

    /**
     * @var string|null
     */
    private $pdf;

    /**
     * @param Payment $payment
     * @param string $pdf
     */
    public function __construct(Payment $payment, $pdf)
    {
        $this->payment = $payment;
        $this->pdf = $pdf;
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
     * @param  \Coyote\User  $user
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($user)
    {
        $mail = (new MailMessage)
            ->subject(sprintf('Potwierdzenie płatności za promowanie oferty: %s', $this->payment->job->title))
            ->line(
                sprintf(
                    'Otrzymaliśmy płatność w kwocie <strong>%s %s</strong>.',
                    $this->payment->invoice->grossPrice(),
                    $this->payment->invoice->currency->symbol
                )
            );

        if ($this->pdf !== null) {
            $mail->line('W załączniku znajdziesz fakturę VAT.');
        }

        $mail
            ->line(
                sprintf(
                    'Twoje ogłoszenie <strong>%s</strong> jest już promowane w naszym serwisie.',
                    $this->payment->job->title
                )
            )
            ->action('Zobacz ogłoszenie', route('job.offer', [$this->payment->job->id, $this->payment->job->slug]))
            ->line('Dziekujemy za skorzystanie z naszych usług.');

        if ($this->pdf !== null) {
            $mail->attachData($this->pdf, $this->getFilename());
        }

        return $mail;
    }

    /**
     * @return string
     */
    private function getFilename(): string
    {
        return str_replace('/', '_', $this->payment->invoice->number) . '.pdf';
    }
}
