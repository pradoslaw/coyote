<?php

namespace Coyote\Listeners;

ini_set('memory_limit', '1G');

use Carbon\Carbon;
use Coyote\Events\PaymentPaid;
use Coyote\Notifications\SuccessfulPaymentNotification;
use Coyote\Payment;
use Coyote\Services\Elasticsearch\Crawler;
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
        $payment = $event->payment;

        app(Connection::class)->transaction(function () use ($payment) {
            $pdf = null;

            // set up invoice only if firm name was provided. it's required!
            if ($payment->invoice_id && $payment->invoice->name) {
                // set up invoice number since it's already paid.
                $this->enumerator->enumerate($payment->invoice);
                // create pdf
                $pdf = $this->pdf->create($payment);
            }

            $payment->status_id = Payment::PAID;

            // establish plan's finish date
            $payment->starts_at = Carbon::now();
            $payment->ends_at = Carbon::now()->addDays($payment->days);

            if ($payment->coupon) {
                $payment->coupon->delete();
            }

            $payment->save();

            foreach ($payment->plan->benefits as $benefit) {
                if ($benefit !== 'is_social' && $benefit === 'is_boost') { // column is_social does not exist in table
                    $payment->job->{$benefit} = true;
                }
            }

            $payment->job->boost_at = Carbon::now();
            $payment->job->deadline_at = max($payment->job->deadline_at, $payment->ends_at);
            $payment->job->save();

            // index job offer
            (new Crawler())->index($payment->job);

            // send email with invoice
            $payment->job->user->notify(
                new SuccessfulPaymentNotification($payment, $pdf)
            );
        });
    }
}
