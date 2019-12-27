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
     * @var int
     */
    public $delay = 30;

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
            $pdf = null;

            // set up invoice only if firm name was provided. it's required!
            if ($event->payment->invoice_id && $event->payment->invoice->name) {
                // set up invoice number since it's already paid.
                $this->enumerator->enumerate($event->payment->invoice);
                // create pdf
                $pdf = $this->pdf->create($event->payment);
            }

            $event->payment->status_id = Payment::PAID;

            // establish plan's finish date
            $event->payment->starts_at = Carbon::now();
            $event->payment->ends_at = Carbon::now()->addDays($event->payment->days);

            if ($event->payment->coupon) {
                $event->payment->coupon->delete();
            }

            $event->payment->save();

            foreach ($event->payment->plan->benefits as $benefit) {
                if ($benefit !== 'is_social') { // column is_social does not exist in table
                    $event->payment->job->{$benefit} = true;
                }
            }

            $event->payment->job->boost_at = Carbon::now();
            $event->payment->job->deadline_at = max($event->payment->job->deadline_at, $event->payment->ends_at);
            $event->payment->job->save();

            // index job offer
            $event->payment->job->putToIndex();

            // send email with invoice
            $event->payment->job->user->notify(
                new SuccessfulPaymentNotification($event->payment, $pdf)
            );
        });
    }
}
