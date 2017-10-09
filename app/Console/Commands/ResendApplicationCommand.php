<?php

namespace Coyote\Console\Commands;

use Coyote\Http\Factories\MailFactory;
use Coyote\Mail\ApplicationSent;
use Coyote\Repositories\Contracts\JobRepositoryInterface;
use Illuminate\Console\Command;

class ResendApplicationCommand extends Command
{
    use MailFactory;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:resend  {--id=} {--email=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-send job\'s offers application.';

    /**
     * @var JobRepositoryInterface
     */
    protected $job;

    /**
     * @param JobRepositoryInterface $job
     */
    public function __construct(JobRepositoryInterface $job)
    {
        parent::__construct();

        $this->job = $job;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $job = $this->job->findOrFail($this->option('id'));
        $mailer = $this->getMailFactory();

        foreach ($job->applications as $application) {
            // we don't queue mail because it has attachment and unfortunately we can't serialize binary data
            $mailer->to($this->option('email'))->send(new ApplicationSent($application, $job));
            $this->line("Sending to: " . $this->option('email'));
        }

        $this->line('Done.');
    }
}
