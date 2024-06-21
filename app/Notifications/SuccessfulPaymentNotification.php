<?php
namespace Coyote\Notifications;

use Coyote\Payment;
use Coyote\Services\UrlBuilder;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SuccessfulPaymentNotification extends Notification
{
    public function __construct(private Payment $payment, private ?string $pdf)
    {
    }

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
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
                        $this->payment->invoice->currency->symbol,
                    ),
                );
        }

        if ($this->payment->coupon_id) {
            // load coupons even if they are deleted
            $this->payment->load([
                'coupon' => function ($query) {
                    return $query->withTrashed();
                },
            ]);

            $mail->line(sprintf('Potwierdzamy realizację kodu rabatowego: <strong>%s</strong>', $this->payment->coupon->code));
        }

        if ($this->pdf !== null) {
            $mail->line('W załączniku znajdziesz fakturę VAT.');
        }

        $mail
            ->line("Twoje ogłoszenie <strong>{$this->payment->job->title}</strong> jest już promowane w naszym serwisie.")
            ->action('Zobacz ogłoszenie', UrlBuilder::job($this->payment->job, true))
            ->line('Dziekujemy za skorzystanie z naszych usług.');

        if ($this->pdf !== null) {
            $mail->attachData($this->pdf, $this->getFilename());
        }

        return $mail;
    }

    private function getFilename(): string
    {
        return str_replace('/', '_', $this->payment->invoice->number) . '.pdf';
    }

    private function getSubject(): string
    {
        if ($this->pdf === null) {
            return 'Potwierdzenie publikacji ogłoszenia: ' . $this->payment->job->title;
        }
        return 'Faktura VAT: ' . $this->payment->job->title;
    }
}
