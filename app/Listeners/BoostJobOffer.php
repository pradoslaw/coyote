<?php
namespace Coyote\Listeners;

ini_set('memory_limit', '1G');

use Carbon\Carbon;
use Coyote\Events\PaymentPaid;
use Coyote\Job;
use Coyote\Notifications\SuccessfulPaymentNotification;
use Coyote\Payment;
use Coyote\Plan;
use Coyote\Services\Elasticsearch\Crawler;
use Coyote\Services\Invoice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Connection;

class BoostJobOffer implements ShouldQueue
{
    public int $delay = 30;

    public function __construct(private Invoice\Enumerator $enumerator, private Invoice\Pdf $pdf) {}

    public function handle(PaymentPaid $event): void
    {
        app(Connection::class)->transaction(fn() => $this->handlePaidPayment($event->payment));
    }

    private function handlePaidPayment(Payment $payment): void
    {
        if ($this->shouldGenerateInvoice($payment)) {
            $this->enumerator->enumerate($payment->invoice); // set up invoice number since it's already paid.
            $pdf = $this->pdf->create($payment);
        } else {
            $pdf = null;
        }
        $payment->status_id = Payment::PAID;
        $payment->starts_at = Carbon::now();
        $payment->ends_at = Carbon::now()->addDays($payment->days);
        if ($payment->coupon) {
            $payment->coupon->delete();
        }
        $payment->save();
        $this->publishJob($payment->job, $payment->plan, $payment->ends_at);
        $this->indexJobOffer($payment);
        $this->sendEmailWithInvoice($payment, $pdf);
    }

    private function shouldGenerateInvoice(Payment $payment): bool
    {
        return $payment->invoice_id && $payment->invoice->name && $payment->invoice->netPrice();
    }

    private function publishJob(Job $job, Plan $plan, Carbon $endsAt): void
    {
        foreach ($plan->benefits as $benefit) {
            if ($benefit !== 'is_social') { // column is_social does not exist in table
                $job->{$benefit} = true;
            }
        }
        $job->boost_at = Carbon::now();
        $job->deadline_at = max($job->deadline_at, $endsAt);
        $job->save();
    }

    private function indexJobOffer(Payment $payment): void
    {
        new Crawler()->index($payment->job);
    }

    private function sendEmailWithInvoice(Payment $payment, ?string $pdf): void
    {
        $notification = new SuccessfulPaymentNotification($payment, $pdf);
        $payment->job->user->notify($notification);
    }
}
