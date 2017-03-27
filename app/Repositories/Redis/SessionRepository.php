<?php

namespace Coyote\Repositories\Redis;

use Coyote\Repositories\Contracts\SessionRepositoryInterface;
use Coyote\Session;
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
     * @inheritdoc
     */
    public function set(string $sessionId, array $payload)
    {
        $this->redis->set($sessionId, serialize($payload));
        $this->redis->sadd('sessions', $sessionId);
    }

    /**
     * @inheritdoc
     */
    public function get(string $sessionId)
    {
        return $this->redis->get($sessionId);
    }

    /**
     * @inheritdoc
     */
    public function destroy(string $sessionId)
    {
        $this->redis->srem('sessions', $sessionId);
        $this->redis->del($sessionId);
    }

    /**
     * @inheritdoc
     */
    public function getByPath($path = null)
    {
        $collection = $this->all();

        if ($path === null) {
            return $collection;
        }

        return $collection->filter(function ($item) use ($path) {
            $urlPath = parse_url($item['url'], PHP_URL_PATH);

            return starts_with($path, $urlPath);
        });
    }

    /**
     * @inheritdoc
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

        return collect(array_map(
            function ($item) {
                return new Session(unserialize($item));
            },
            $result
        ));
    }

    /**
     * @inheritdoc
     */
    public function gc(int $lifetime)
    {
        foreach ($this->all() as $item) {
            if ($item->expired($lifetime)) {
                $this->redis->del($item['id']);
                $this->redis->srem('sessions', $item['id']);
            }
        }

        return true;
    }

    protected function makeRedis()
    {
        $this->redis = $this->app['redis'];
    }
}
