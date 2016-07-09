<?php

namespace Coyote\Listeners;

use Coyote\Repositories\Contracts\FirewallRepositoryInterface;
use Coyote\Repositories\Contracts\GroupRepositoryInterface;
use Coyote\Repositories\Contracts\MicroblogRepositoryInterface;
use Coyote\Repositories\Contracts\PastebinRepositoryInterface;
use Coyote\Repositories\Contracts\TopicRepositoryInterface;
use Coyote\Repositories\Contracts\WikiRepositoryInterface;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Container\Container as App;

class BindRouteDefaultModel
{
    /**
     * @var array
     */
    protected $default = [
        'topic' => TopicRepositoryInterface::class,
        'microblog' => MicroblogRepositoryInterface::class,
        'wiki' => WikiRepositoryInterface::class,
        'pastebin' => PastebinRepositoryInterface::class,
        'firewall' => FirewallRepositoryInterface::class,
        'group' => GroupRepositoryInterface::class
    ];

    /**
     * @var App
     */
    protected $app;

    /**
     * Create the event listener.
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * Handle the event.
     *
     * @param RouteMatched $event
     */
    public function handle(RouteMatched $event)
    {
        $optional = $this->getOptionalParameters($event->route->getUri());

        foreach ($optional as $parameter) {
            if (isset($this->default[$parameter]) && null === $event->route->getParameter($parameter)) {
                $model = $this->app->make($this->default[$parameter])->makeModel();
                $event->route->setParameter($parameter, $model);
            }
        }
    }

    /**
     * @param string $uri
     * @return array
     */
    protected function getOptionalParameters($uri)
    {
        $segments = explode('/', $uri);
        $optional = [];

        foreach ($segments as $segment) {
            $len = strlen($segment);
            if ($len > 0 && $segment[0] === '{' && $segment[$len - 1] === '}' && $segment[$len - 2] === '?') {
                $optional[] = substr($segment, 1, -2);
            }
        }

        return $optional;
    }
}
