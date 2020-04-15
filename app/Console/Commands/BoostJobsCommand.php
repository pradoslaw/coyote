<?php

namespace Coyote\Console\Commands;

use Carbon\Carbon;
use Coyote\Repositories\Contracts\PaymentRepositoryInterface;
use Coyote\Services\Elasticsearch\Crawler;
use Illuminate\Console\Command;

class BoostJobsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:boost';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Boost premium jobs.';

    /**
     * @var PaymentRepositoryInterface
     */
    protected $payment;

    /**
     * @param PaymentRepositoryInterface $payment
     */
    public function __construct(PaymentRepositoryInterface $payment)
    {
        parent::__construct();

        $this->payment = $payment;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $payments = $this->payment->ongoingPaymentsWithBoostBenefit();
        $crawler = new Crawler();

        foreach ($payments as $payment) {
            $every = floor($payment->days / max(2, $payment->plan->boost));

            // attention! job can be already removed.
            if ($payment->job !== null && Carbon::now() >= Carbon::parse($payment->job->boost_at)->addDays($every)) {
                $payment->job->boost_at = Carbon::now();
                $payment->job->save();

                $this->info("Boosting {$payment->job->title}");

                $crawler->index($payment->job);
            }
        }

        $this->info('Done.');
    }
}
