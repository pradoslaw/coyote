<?php

namespace Coyote\Console\Commands\Elasticsearch;

use Coyote\Searchable;
use Illuminate\Console\Command;
use Illuminate\Container\Container as App;

class Mapping extends Command
{
    use EsTrait;
    
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
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->option('force') || $this->confirm('Do you want to create Elasticsearch mapping?', true)) {
            $model = ucfirst($this->option('model'));

            if (!$model) {
                $this->all();
            } else {
                $this->one($model);
            }
        }
    }

    /**
     * @param string $model
     */
    private function one($model)
    {
        $model = $this->app->make('Coyote\\' . $model);

        if ($model instanceof Searchable) {
            $this->error(get_class($model) . " has to implement Searchable trait.");
        }

        $model->putMapping();
        $this->info('Done.');
    }

    private function all()
    {
        foreach ($this->getSuitableModels() as $className) {
            $model = $this->app->make($className);
            $model->putMapping();

            $this->info($className . '... Done.');
        }
    }
}
