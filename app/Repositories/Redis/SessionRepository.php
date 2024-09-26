<?php
namespace Coyote\Repositories\Redis;

use Coyote\Session;
use Illuminate\Container\Container as App;

class SessionRepository
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
        return $this->redis->hset(self::REDIS_KEY, $sessionId, serialize($payload));
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
        return $this->redis->hdel(self::REDIS_KEY, $sessionId);
    }

    /**
     * @inheritdoc
     */
    public function all()
    {
        $result = $this->unserialize($this->redis->hvals(self::REDIS_KEY));

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
