<?php

namespace Coyote\Listeners;

use Carbon\Carbon;
use Coyote\Events\PaymentPaid;
use Coyote\Notifications\SuccessfulPaymentNotification;
use Coyote\Payment;
use Illuminate\Database\Connection;
use Illuminate\Contracts\Queue\ShouldQueue;
use Coyote\Services\Invoice\Enumerator as InvoiceEnumerator;
use Coyote\Services\Invoice\Pdf as InvoicePdf;

class BoostJobOffer implements ShouldQueue
{
    /**
     * @var InvoiceEnumerator
     */
    private $enumerator;

    /**
     * @var InvoicePdf
     */
    private $pdf;

    /**
     * @param InvoiceEnumerator $enumerator
     * @param InvoicePdf $pdf
     */
    public function __construct(InvoiceEnumerator $enumerator, InvoicePdf $pdf)
    {
        $this->enumerator = $enumerator;
        $this->pdf = $pdf;
    }

    /**
     * Handle the event.
     *
     * @param  PaymentPaid  $event
     * @return void
     */
    public function handle(PaymentPaid $event)
    {
        app(Connection::class)->transaction(function () use ($event) {
            // set up invoice number since it's already paid.
            $this->enumerator->enumerate($event->payment->invoice);

            $event->payment->status_id = Payment::PAID;

            // establish plan's finish date
            $event->payment->starts_at = Carbon::now();
            $event->payment->ends_at = Carbon::now()->addDays($event->payment->days);

            $event->payment->save();

            // boost job offer so it's on the top of the list
            $event->payment->job->boost = true;
            $event->payment->job->deadline_at = max($event->payment->job->deadline_at, $event->payment->ends_at);
            $event->payment->job->save();

            // send email with invoice
            $event->user->notify(
                new SuccessfulPaymentNotification($event->payment, $this->pdf->create($event->payment))
            );
        });
    }
}
