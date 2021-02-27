<?php

namespace Tests\Feature\Console\Commands;

use Carbon\Carbon;
use Coyote\Events\PaymentPaid;
use Coyote\Job;
use Coyote\Notifications\SuccessfulPaymentNotification;
use Coyote\Payment;
use Coyote\Plan;
use Coyote\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BoostJobsCommandTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    public function testBoostJobWithPlusPlan()
    {
        $plan = Plan::where('name', 'Plus')->get()->first();

        /** @var Job $job */
        $job = factory(Job::class)->create(['plan_id' => $plan->id]);

        event(new PaymentPaid($job->getUnpaidPayment()));

        Notification::assertSentTo([$job->user], SuccessfulPaymentNotification::class);

        $now = now();

        for ($i = 1; $i <= 40; $i++) {
            Carbon::setTestNow($now->addDay());
            $output = $i === 20 ? "Boosting " . $job->title : "Done.";

            $this->artisan('job:boost')
                ->expectsOutput($output);
        }
    }

    public function testBoostJobWithPremiumPlan()
    {
        $plan = Plan::where('name', 'Premium')->get()->first();

        /** @var Job $job */
        $job = factory(Job::class)->create(['plan_id' => $plan->id]);

        event(new PaymentPaid($job->getUnpaidPayment()));

        $job->refresh();

        $this->assertTrue($job->is_publish);
        $this->assertTrue($job->is_ads);
        $this->assertTrue($job->is_on_top);

        $now = now();

        for ($i = 1; $i <= $plan->length; $i++) {
            Carbon::setTestNow($now->addDay());
            $output = $i == 10 || $i == 20 || $i == 31 ? "Boosting " . $job->title : "Done.";

            $this->artisan('job:boost')
                ->expectsOutput($output);
        }
    }
}
