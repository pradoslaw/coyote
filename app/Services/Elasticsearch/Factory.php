<?php

namespace Coyote\Services\Elasticsearch;

use Coyote\Services\Elasticsearch\Strategies\CommonStrategy;
use Coyote\Services\Elasticsearch\Strategies\StrategyInterface;
use Coyote\Services\JwtToken;
use Illuminate\Contracts\Container\Container as App;
use Illuminate\Http\Request;

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
        /** @var JwtToken $jwtToken */
        $jwtToken = $this->app[JwtToken::class];

        /** @var Api $api */
        $api = $this->app[Api::class];
        $api->setJwtToken(
            $jwtToken->token($this->app[Request::class]->user())
        );

        $class = $this->getClass($model);
        $class->setApi($api);

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
            return new CommonStrategy();
        }

        return $this->app[$class];
    }
}
