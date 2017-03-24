<?php

namespace Coyote\Repositories\Redis;

use Coyote\Repositories\Contracts\SessionRepositoryInterface;
use Illuminate\Container\Container as App;

class SessionRepository implements SessionRepositoryInterface
{
    /**
     * @var App
     */
    protected $app;

    /**
     * @var mixed
     */
    protected $redis;

    /**
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;

        $this->makeRedis();
    }

    /**
     * @param string|null $path
     * @return \Illuminate\Support\Collection|static
     */
    public function getByPath($path = null)
    {
        $collection = collect($this->all());

        if ($path === null) {
            return $collection;
        }

        return $collection->filter(function ($item) use ($path) {
            $urlPath = parse_url($item['url'], PHP_URL_PATH);

            return starts_with($path, $urlPath);
        });
    }

    /**
     * @return mixed
     */
    public function all()
    {
        $sessionIds = $this->redis->smembers('sessions');

        $result = $this->redis->pipeline(function ($pipe) use ($sessionIds) {
            $result = [];

            foreach ($sessionIds as $sessionId) {
                $result[] = $pipe->get($sessionId);
            }

            return $result;
        });

        return array_map('unserialize', $result);
    }

    protected function makeRedis()
    {
        $this->redis = $this->app['redis'];
    }
}
