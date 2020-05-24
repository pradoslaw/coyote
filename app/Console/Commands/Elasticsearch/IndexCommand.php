<?php

namespace Coyote\Console\Commands\Elasticsearch;

use Coyote\Repositories\Contracts\JobRepositoryInterface;
use Coyote\Repositories\Contracts\PostRepositoryInterface;
use Coyote\Services\Elasticsearch\Crawler;
use Illuminate\Console\Command;
use Illuminate\Container\Container as App;
use Illuminate\Database\Query\Expression;

class IndexCommand extends Command
{
    use EsTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'es:index {--model=} {--force}';

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
     * @var App
     */
    protected $app;

    /**
     * @param App $app
     */
    public function __construct(App $app)
    {
        parent::__construct();

        $this->app = $app;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('force') || $this->confirm('Do you want to index data in Elasticsearch?', true)) {
            $this->dispatch();
            $this->info('Done.');
        }
    }

    /**
     * @param string $model
     */
    private function one($model)
    {
        $className = 'Coyote\\' . ucfirst(strtolower($model));

        $this->index($className);
    }

    private function all()
    {
        foreach ($this->getSuitableModels() as $className) {
            $this->index($className);
        }
    }

    /**
     * @param string $className
     */
    private function index($className)
    {
        $model = $this->app->make($className);
        $this->line("Indexing $className ...");

        $builder = $model->select()->orderBy('id', 'desc');//->where('id', 44030);
        $objectName = get_class($model);

        // ugly hack for job offers...
        if ($objectName === 'Coyote\Job') {
            $builder = $builder->where('deadline_at', '>=', new Expression('NOW()'))->where('is_publish', 1)->with('firm');
        } elseif ($objectName === 'Coyote\Microblog') {
            $builder = $builder->whereNull('parent_id');
        }

        $bar = $this->output->createProgressBar($builder->count());
        $crawler = new Crawler();

        $builder->chunk(20000, function ($rowset) use ($bar, $crawler) {
            foreach ($rowset as $row) {
                $crawler->index($row);

                $bar->advance();
            }
        });

        $bar->finish();
        $this->info("\n" . $className . '... Done.');
    }
}
