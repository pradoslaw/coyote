<?php

namespace Coyote\Console\Commands;

use Coyote\Http\Factories\MailFactory;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Services\Elasticsearch\ResultSet;
use Coyote\User;
use Elasticsearch\Client;
use Illuminate\Console\Command;
use Illuminate\Mail\Message;

class PurgeJobs extends Command
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
     * @var array
     */
    private $params = [];

    /**
     * @param UserRepository $user
     */
    public function __construct(UserRepository $user)
    {
        parent::__construct();

        $this->elasticsearch = app('elasticsearch');
        $this->params = [
            'index' => config('elasticsearch.default_index'),
            'type'  => 'jobs',
            'body'  => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['range' => ['deadline_at' => ['lt' => 'now']]]
                        ]
                    ]
                ]
            ]
        ];

        $this->user = $user;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $result = new ResultSet($this->elasticsearch->search($this->params));

        foreach ($result as $hit) {
            $user = $this->user->find($hit['user_id'], ['name', 'email']);
            $this->elasticsearch->delete(
                ['id' => $hit['id'], 'index' => config('elasticsearch.default_index'), 'type' => 'jobs']
            );

            if ($user->email) {
                $this->sendEmail($user, $hit);
                $this->info(sprintf('Sending e-mail about ending offer: %s.', $hit['title']));
            }
        }
    }

    /**
     * @param User $user
     * @param array $hit
     */
    private function sendEmail(User $user, array $hit)
    {
        $this->getMailFactory()->queue('emails.job.reminder', $hit, function (Message $message) use ($hit, $user) {
            $message->subject('Twoje ogłoszenie "' . $hit['title'] . '" wygasło.');
            $message->to($user->email);
        });
    }
}
