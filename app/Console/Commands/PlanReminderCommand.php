<?php

namespace Coyote\Console\Commands;

use Carbon\Carbon;
use Coyote\Mail\PlanReminder;
use Illuminate\Contracts\Mail\Mailer;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Repositories\Contracts\MailRepositoryInterface as MailRepository;
use Coyote\Repositories\Contracts\PaymentRepositoryInterface as PaymentRepository;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Repositories\Criteria\Job\PriorDate;
use Illuminate\Console\Command;

class PlanReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:plan-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send plan reminder to our clients.';

    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @var MailRepository
     */
    protected $mail;

    /**
     * @var JobRepository
     */
    protected $job;

    /**
     * @var UserRepository
     */
    protected $user;

    /**
     * @var PaymentRepository
     */
    protected $payment;

    /**
     * @param Mailer $mailer
     * @param MailRepository $mail
     * @param JobRepository $job
     * @param UserRepository $user
     * @param PaymentRepository $payment
     */
    public function __construct(
        Mailer $mailer,
        MailRepository $mail,
        JobRepository $job,
        UserRepository $user,
        PaymentRepository $payment
    ) {
        parent::__construct();

        $this->mailer = $mailer;
        $this->mail = $mail;
        $this->job = $job;
        $this->user = $user;
        $this->payment = $payment;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // get only jobs from last 24hs
        $this->job->pushCriteria(new PriorDate(Carbon::yesterday()));

        $jobs = $this->job->all()->groupBy('user_id')->reject(function ($value, $userId) {
            // skip users who made payment in last 7 days
            return $this->payment->hasRecentlyPaid($userId);
        });

        $this->sendEmails($jobs);

        $this->info('Done.');
    }

    private function sendEmails($collection)
    {
        foreach ($collection as $userId => $jobs) {
            $user = $this->user->find($userId);

            $mail = (new PlanReminder($jobs))->to($user);

            if (!$this->mail->isDuplicated($mail, 7)) {
                $this->mailer->send($mail);

                $this->info("Sending e-mail to $user->email");
            }
        }
    }
}
