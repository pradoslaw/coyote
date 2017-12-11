<?php

namespace Coyote\Console\Commands;

use Coyote\Http\Factories\MailFactory;
use Coyote\Job\Application;
use Coyote\Mail\ApplicationSent;
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
     * Execute the console command.
     */
    public function handle()
    {
        $application = Application::findOrFail($this->option('id'));

        $mailer = $this->getMailFactory();
        $email = $this->option('email') ?: $application->job->email;

        // we don't queue mail because it has attachment and unfortunately we can't serialize binary data
        $mailer->to($email)->send(new ApplicationSent($application, $application->job));
        $this->line("Sending to: $email");

        $this->line('Done.');
    }
}
