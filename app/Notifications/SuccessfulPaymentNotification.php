<?php

namespace Coyote\Notifications;

use Coyote\Payment;
use Coyote\Services\UrlBuilder;
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
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail()
    {
        $mail = (new MailMessage)
            ->subject($this->getSubject());

        if ($this->payment->invoice_id && $this->payment->invoice->grossPrice() > 0) {
            $mail
                ->bcc(config('mail.from.address'))
                ->line(
                    sprintf(
                        'Otrzymaliśmy płatność w kwocie <strong>%s %s</strong>.',
                        $this->payment->invoice->grossPrice(),
                        $this->payment->invoice->currency->symbol
                    )
                );
        }

        if ($this->payment->coupon_id) {
            // load coupons even if they are deleted
            $this->payment->load([
                'coupon' => function ($query) {
                    return $query->withTrashed();
                }
            ]);

            $mail->line(sprintf('Potwierdzamy realizację kodu rabatowego: <strong>%s</strong>', $this->payment->coupon->code));
        }

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
            ->action('Zobacz ogłoszenie', UrlBuilder::job($this->payment->job, true))
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

    /**
     * @return string
     */
    private function getSubject(): string
    {
        return sprintf(
            $this->pdf !== null
                ? 'Faktura VAT: %s'
                    : 'Potwierdzenie publikacji ogłoszenia: %s',
            $this->payment->job->title
        );
    }
}
