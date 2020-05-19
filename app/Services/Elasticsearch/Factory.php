<?php

namespace Coyote\Services\Elasticsearch;

use Coyote\Services\Elasticsearch\Strategies\CommonStrategy;
use Coyote\Services\Elasticsearch\Strategies\StrategyInterface;
use Illuminate\Contracts\Container\Container as App;

class Factory
{
    /**
     * @var App
     */
    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * @param string|null $model
     * @return StrategyInterface
     */
    public function make(?string $model): StrategyInterface
    {
        $class = $this->getClass($model);
        $class->setApi($this->app[Api::class]);

        return $class;
    }

    /**
     * @param string|null $model
     * @return StrategyInterface
     */
    private function getClass(?string $model): StrategyInterface
    {
        if (!$model) {
            return new CommonStrategy();
        }

        $class = __NAMESPACE__ . '\\Strategies\\' . ucfirst($model) . 'Strategy';

        if (!class_exists($class, true)) {
            throw new \InvalidArgumentException("Can't find $class class.");
        }

        return new $class;
    }
}
