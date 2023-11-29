<?php
namespace Coyote\Console\Commands\Elasticsearch;

use Coyote\Services\Elasticsearch\Crawler;
use Illuminate\Console\Command;
use Illuminate\Container\Container as App;
use Illuminate\Database\Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;
use Symfony\Component\Console\Helper\ProgressBar;

class IndexCommand extends Command
{
    protected $signature = 'es:index {--model=} {--force} {--limit=}';
    protected $description = 'Index data in Elasticsearch.';

    private App $app;
    private array $elasticSearchModels;

    public function __construct(App $app)
    {
        parent::__construct();
        $this->app = $app;
        $this->elasticSearchModels = [
            'firm'      => \Coyote\Firm::class,
            'job'       => \Coyote\Job::class,
            'microblog' => \Coyote\Microblog::class,
            'page'      => \Coyote\Page::class,
            'post'      => \Coyote\Post::class,
            'stream'    => \Coyote\Stream::class,
            'tag'       => \Coyote\Tag::class,
            'topic'     => \Coyote\Topic::class,
            'user'      => \Coyote\User::class,
            'wiki'      => \Coyote\Wiki::class,
        ];
    }

    public function handle(): int
    {
        $limit = $this->option('limit');
        $modelBuilders = $this->modelBuilders();
        if ($this->presentAndConfirm($modelBuilders, $limit)) {
            foreach ($modelBuilders as $model) {
                $this->indexModelSince($model, $limit);
            }
            $this->info('Done.');
        }
        return 0;
    }

    private function presentAndConfirm(array $modelBuilders, ?int $limit): bool
    {
        $this->printCountTable($modelBuilders, $limit);
        if ($this->option('force')) {
            return true;
        }
        return $this->confirm('Do you want to index the given data in ElasticSearch?', true);
    }

    private function printCountTable(array $modelBuilders, ?int $limit): void
    {
        $rows = [];
        foreach ($modelBuilders as $slug => $builder) {
            $rows[] = [$slug, $this->builderCount($builder, $limit)];
        }
        $this->table(['Model', 'Remaining rows'], $rows, 'box-double');
    }

    private function indexModelSince(Eloquent\Builder $builder, ?int $limit): void
    {
        $this->printStatusStart($builder->getModel());
        $this->startIndexing($builder, $limit);
        $this->printStatusEnd($builder->getModel());
    }

    private function startIndexing(Eloquent\Builder $builder, ?int $limit): void
    {
        $bar = $this->output->createProgressBar($this->builderCount($builder, $limit));
        $page = 0;
        $pageSize = 20000;
        $alreadyLoaded = 0;
        while (true) {
            $leftToLoad = $limit - $alreadyLoaded;
            if ($leftToLoad <= 0) {
                break;
            }
            $models = $builder->forPage($page, \min($pageSize, $leftToLoad))->get();
            if ($models->count() === 0) {
                break;
            }
            $alreadyLoaded += $models->count();
            $this->nextChunk($models, $bar);
            if ($models->count() < $limit) {
                break;
            }
            ++$page;
        }
        $bar->finish();
    }

    function nextChunk(Eloquent\Collection $models, ProgressBar $bar): void
    {
        $crawler = new Crawler();
        foreach ($models as $model) {
            $crawler->index($model);
            $bar->advance();
        }
    }

    private function modelBuilder(Model $model): Eloquent\Builder
    {
        $builder = $model->newQuery()
            ->select()
            ->orderBy('id', 'desc');

        if (get_class($model) === \Coyote\Job::class) {
            return $builder
                ->where('deadline_at', '>=', new Expression('NOW()'))
                ->where('is_publish', 1)
                ->with('firm');
        }
        if (get_class($model) === \Coyote\Microblog::class) {
            return $builder->whereNull('parent_id');
        }
        return $builder;
    }

    private function printStatusStart(Model $model): void
    {
        $this->line("Indexing " . \get_class($model) . " ...");
    }

    private function printStatusEnd(Model $model): void
    {
        $this->info("\n" . \get_class($model) . '... Done.');
    }

    private function modelBuilders(): array
    {
        $builders = [];
        foreach ($this->modelClassNamesToIndex() as $slug => $builder) {
            $builders[$slug] = $this->modelBuilder($this->app->make($builder));
        }
        return $builders;
    }

    private function modelClassNamesToIndex(): array
    {
        $modelSlug = $this->option('model');
        if ($modelSlug === null) {
            return $this->elasticSearchModels;
        }
        return [$modelSlug => $this->modelClassNameBySlug($modelSlug)];
    }

    private function modelClassNameBySlug(string $model): string
    {
        if (\array_key_exists($model, $this->elasticSearchModels)) {
            return $this->elasticSearchModels[$model];
        }
        throw new \Exception(\implode("\n", [
            "Failed to find model: $model",
            'Existing models: ' . implode(', ', \array_keys($this->elasticSearchModels)) . '.',
        ]));
    }

    private function builderCount(Eloquent\Builder $builder, ?int $limit): int
    {
        $totalCount = $builder->count();
        if ($limit === null) {
            return $totalCount;
        }
        return \min($totalCount, $limit);
    }
}
