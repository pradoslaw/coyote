<?php

namespace Coyote\Console\Commands;

use Coyote\Repositories\Contracts\TopicRepositoryInterface as Topic;
use Coyote\Topic\Visit;
use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\SelfHandling;

class PurgeViews extends Command implements SelfHandling
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coyote:counter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Increment topic views';

    /**
     * @var Topic
     */
    private $topic;

    /**
     * Create a new command instance.
     *
     * @param Topic $topic
     */
    public function __construct(Topic $topic)
    {
        parent::__construct();

        $this->topic = $topic;
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $redis = app('redis');
        $keys = $redis->keys('counter:topic:*');

        foreach ($keys as $key) {
            // $id stands for topic id
            list(, , $id) = explode(':', $key);
            // fetch topic views
            $views = $redis->smembers($key);

            foreach ($views as $view) {
                list($user, ) = explode(';', $view);

                if (is_numeric($user)) {
                    $visit = Visit::firstOrNew(['topic_id' => $id, 'user_id' => $user]);
                    $visit->visits++;

                    $visit->save();
                }
            }

            // add views to topic
            $this->topic->addViews($id, count($views));
            $this->info('Added ' . count($views) . ' views to topic #' . $id);

            $redis->del($key);
        }
    }
}
