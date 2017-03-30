<?php

namespace Coyote\Repositories\Redis;

use Coyote\Repositories\Contracts\SessionRepositoryInterface;
use Coyote\Session;
use Illuminate\Container\Container as App;

class SessionRepository implements SessionRepositoryInterface
{
    const REDIS_KEY = 'sessions';

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
        $this->redis->hset(self::REDIS_KEY, $sessionId, serialize($payload));
    }

    /**
     * @inheritdoc
     */
    public function get(string $sessionId)
    {
        return $this->redis->hget(self::REDIS_KEY, $sessionId);
    }

    /**
     * @inheritdoc
     */
    public function destroy(string $sessionId)
    {
        $this->redis->hdel(self::REDIS_KEY, $sessionId);
    }

    /**
     * @inheritdoc
     */
    public function getByPath($path = null)
    {
        if ($path === null) {
            return $this->all();
        }

        $collection = $this->unserialize($this->redis->hvals(self::REDIS_KEY));

        return $collection
            ->filter(function ($item) use ($path) {
                $sessionPath = parse_url($item['url'], PHP_URL_PATH);

                return starts_with($sessionPath, $path);
            })
            ->map(function ($item) {
                return $this->makeModel($item);
            });
    }

    /**
     * @inheritdoc
     */
    public function all()
    {
        start_measure('redis collect sessions');
        $result = $this->unserialize($this->redis->hvals(self::REDIS_KEY));
        stop_measure('redis collect sessions');

        return $result->map(function ($item) {
            return $this->makeModel($item);
        });
    }

    /**
     * @inheritdoc
     */
    public function gc(int $lifetime)
    {
        foreach ($this->all() as $item) {
            if ($item->expired($lifetime)) {
                $this->redis->hdel(self::REDIS_KEY, $item['id']);
            }
        }

        return true;
    }

    /**
     * @param array $item
     * @return Session
     */
    protected function makeModel(array $item): Session
    {
        return new Session($item);
    }

    protected function makeRedis()
    {
        $this->redis = $this->app['redis'];
    }

    /**
     * @param array $rowset
     * @return \Illuminate\Support\Collection
     */
    private function unserialize(array $rowset)
    {
        return collect(array_map('unserialize', $rowset));
    }
}
