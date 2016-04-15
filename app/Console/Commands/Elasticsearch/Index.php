<?php

namespace Coyote\Console\Commands\Elasticsearch;

use Coyote\Repositories\Contracts\JobRepositoryInterface;
use Coyote\Repositories\Contracts\PostRepositoryInterface;
use Illuminate\Console\Command;

class Index extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'es:index {--model=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index data in Elasticsearch.';

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
        if ($this->confirm('Do you want to index data in Elasticsearch?', true)) {
            $model = ucfirst($this->option('model'));

            if (!$model) {
                $this->indexAll();
            } else {
                if (!method_exists($this, "index{$model}")) {
                    $this->error("$model does not exist");
                }

                $this->{'index' . $model}();
            }

            $this->info('Done.');
        }
    }

    private function indexAll()
    {
        foreach (get_class_methods($this) as $method) {
            if ($method !== 'indexAll' && $method !== 'index' && 'index' === substr($method, 0, 5)) {
                $this->$method();
            }
        }
    }

    private function indexPost()
    {
        $this->line('Indexing posts in Elasticsearch...');
        $this->index($this->post);
        $this->info('Success');
    }

    private function indexJob()
    {
        $this->line('Indexing jobs in Elasticsearch...');
        $this->index($this->job->select()->whereNull('deleted_at')->where('deadline_at', '>=', \DB::raw('NOW()')));
        $this->info('Success');
    }

    private function index($model)
    {
        $bar = $this->output->createProgressBar($model->count());

        $model->chunk(10000, function ($rowset) use ($bar) {
            foreach ($rowset as $row) {
                $row->putToIndex();

                $bar->advance();
            }
        });

        $bar->finish();
    }
}
