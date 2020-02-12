<?php

namespace Coyote\Console\Commands;

use Coyote\Http\Factories\MailFactory;
use Coyote\Job;
use Coyote\Notifications\Job\ExpiredNotification;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Services\Elasticsearch\ResultSet;
use Coyote\User;
use Elasticsearch\Client;
use Illuminate\Console\Command;

class PurgeJobsCommand extends Command
{
    use MailFactory;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge expired job offers from Elasticsearch index.';

    /**
     * @var Client
     */
    protected $elasticsearch;

    /**
     * @var UserRepository
     */
    protected $user;

    /**
     * @var JobRepository
     */
    protected $job;

    /**
     * @var array
     */
    private $params = [];

    /**
     * @param UserRepository $user
     * @param JobRepository $job
     */
    public function __construct(UserRepository $user, JobRepository $job)
    {
        parent::__construct();

        $this->elasticsearch = app('elasticsearch');
        $this->params = [
            'index' => config('elasticsearch.default_index'),
            'type'  => '_doc',
            'body'  => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['term' => ['model' => 'job']],
                            ['range' => ['deadline_at' => ['lt' => 'now']]]
                        ]
                    ]
                ],
                'size' => 100
            ]
        ];

        $this->user = $user;
        $this->job = $job;
    }

    /**
     * @return void
     */
    public function handle()
    {
        $result = new ResultSet($this->elasticsearch->search($this->params));

        foreach ($result as $hit) {
            $this->elasticsearch->delete(
                ['id' => "job_$hit[id]", 'index' => config('elasticsearch.default_index'), 'type' => '_doc']
            );

            $user = $this->user->find($hit['user_id'] ?? null, ['name', 'email']);

            if ($user !== null && $user->email) {
                $job = $this->job->find($hit['id']);

                $this->sendEmail($user, $job);
                $this->info(sprintf('Sending e-mail about ending offer: %s.', $job->title));
            }
        }
    }

    /**
     * @param User $user
     * @param Job $job
     */
    private function sendEmail(User $user, Job $job)
    {
        $user->notify(new ExpiredNotification($job));
    }
}
