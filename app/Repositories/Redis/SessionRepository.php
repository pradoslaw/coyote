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
        $this->redis->hset('sessions', $sessionId, serialize($payload));
    }

    /**
     * @inheritdoc
     */
    public function get(string $sessionId)
    {
        return $this->redis->hget('sessions', $sessionId);
    }

    /**
     * @inheritdoc
     */
    public function destroy(string $sessionId)
    {
        $this->redis->hdel('sessions', $sessionId);
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
            $sessionPath = parse_url($item['url'], PHP_URL_PATH);

            return starts_with($sessionPath, $path);
        });
    }

    /**
     * @inheritdoc
     */
    public function all()
    {
        $result = $this->redis->hvals('sessions');

        return collect(array_map(
            function ($item) {
                return $this->makeModel(unserialize($item));
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
                $this->redis->hdel('sessions', $item['id']);
            }
        }

        return true;
    }

    /**
     * @param array $data
     * @return Session
     */
    protected function makeModel(array $data): Session
    {
        return new Session($data);
    }

    protected function makeRedis()
    {
        $this->redis = $this->app['redis'];
    }
}
