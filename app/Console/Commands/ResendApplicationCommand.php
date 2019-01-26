<?php

namespace Coyote\Console\Commands;

use Coyote\Http\Factories\MailFactory;
use Coyote\Job\Application;
use Coyote\Notifications\Job\ApplicationSentNotification;
use Illuminate\Console\Command;

class ResendApplicationCommand extends Command
{
    use MailFactory;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:resend  {--id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-send job\'s offers application.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $application = Application::findOrFail($this->option('id'));

        $email = $application->job->email;
        $application->job->notify(new ApplicationSentNotification($application));

        $this->line("Sending to: $email");

        $this->line('Done.');
    }
}
