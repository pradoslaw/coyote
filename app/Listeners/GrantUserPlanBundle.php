<?php
namespace Coyote\Listeners;

use Coyote\Events\PaymentPaid;
use Coyote\Models\UserPlanBundle;

class GrantUserPlanBundle
{
    public function handle(PaymentPaid $event): void
    {
        $payment = $event->payment;
        if ($payment->plan->bundle_size) {
            $bundle = new UserPlanBundle();
            $bundle->payment()->associate($payment);
            $bundle->plan()->associate($payment->plan);
            $bundle->user()->associate($payment->job->user);
            $bundle->remaining = $payment->plan->bundle_size;
            $bundle->save();
        }
    }
}
