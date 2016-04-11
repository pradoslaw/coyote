<?php

namespace Coyote\Console\Commands\Elasticsearch;

use Coyote\Repositories\Contracts\JobRepositoryInterface;
use Coyote\Repositories\Contracts\PostRepositoryInterface;
use Illuminate\Console\Command;

class Mapping extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'es:mapping {--model=} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Elasticsearch mapping.';

    /**
     * @var JobRepositoryInterface
     */
    protected $job;

    /**
     * @var PostRepositoryInterface
     */
    protected $post;

    /**
     * Mapping constructor.
     * @param PostRepositoryInterface $post
     * @param JobRepositoryInterface $job
     */
    public function __construct(PostRepositoryInterface $post, JobRepositoryInterface $job)
    {
        parent::__construct();

        $this->post = $post;
        $this->job = $job;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {        
        if ($this->option('force') || $this->confirm('Do you want to create Elasticsearch mapping?', true)) {
            $model = ucfirst($this->option('model'));

            if (!$model) {
                $this->putMapping();
            } else {
                if (!method_exists($this, "mapping{$model}")) {
                    $this->error("$model does not exist");
                }

                $this->{'mapping' . $model}();
            }

            $this->info('Done.');
        }
    }

    private function putMapping()
    {
        foreach (get_class_methods($this) as $method) {
            if ('mapping' === substr($method, 0, 7)) {
                $this->$method();
            }
        }
    }

    private function mappingPost()
    {
        $this->post->putMapping();
    }

    private function mappingJob()
    {
        $this->job->putMapping();
    }
}
