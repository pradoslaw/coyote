<?php

namespace Coyote\Console\Commands;

use Coyote\Events\JobWasSaved;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Illuminate\Console\Command;

class ExpireJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Turn off expired premium jobs.';

    /**
     * @var JobRepository
     */
    protected $job;

    /**
     * @param JobRepository $job
     */
    public function __construct(JobRepository $job)
    {
        parent::__construct();

        $this->job = $job;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $jobs = $this->job->getExpiredOffers();

        foreach ($jobs as $job) {
            $job->boost = false;
            $job->save();

            event(new JobWasSaved($job)); // reindex in elasticsearch
        }
    }
}
