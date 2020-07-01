<?php

namespace Tests\Feature\Console\Commands;

use Carbon\Carbon;
use Coyote\Job;
use Coyote\Payment;
use Coyote\Plan;
use Coyote\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BoostJobsCommandTest extends TestCase
{
    use DatabaseTransactions;

    public function testBoostJobWithPlusPlan()
    {
        $plan = Plan::where('name', 'Plus')->get()->first();
        $user = User::first();

        $job = factory(Job::class)->create(['user_id' => $user->id, 'is_publish' => true, 'is_boost' => true, 'boost_at' => Carbon::now()]);
        $job->payments()->create(['status_id' => Payment::PAID, 'plan_id' => $plan->id, 'days' => $plan->length, 'starts_at' => Carbon::now(), 'ends_at' => Carbon::now()->addDays($plan->length)]);

        $now = Carbon::now();

        for ($i = 1; $i <= 40; $i++) {
            Carbon::setTestNow($now->addDay(1));
            $output = $i === 20 ? "Boosting " . $job->title : "Done.";

            $this->artisan('job:boost')
                ->expectsOutput($output);
        }
    }

    public function testBoostJobWithPremiumPlan()
    {
        $plan = Plan::where('name', 'Premium')->get()->first();
        $user = User::first();

        $job = factory(Job::class)->create(['user_id' => $user->id, 'is_publish' => true, 'is_boost' => true, 'boost_at' => Carbon::now()]);
        $job->payments()->create(['status_id' => Payment::PAID, 'plan_id' => $plan->id, 'days' => $plan->length, 'starts_at' => Carbon::now(), 'ends_at' => Carbon::now()->addDays($plan->length)]);

        $now = Carbon::now();

        for ($i = 1; $i <= 40; $i++) {
            Carbon::setTestNow($now->addDay(1));
            $output = $i == 13 || $i == 26 || $i == 39 ? "Boosting " . $job->title : "Done.";

            $this->artisan('job:boost')
                ->expectsOutput($output);
        }
    }
}
