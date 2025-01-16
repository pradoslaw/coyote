<?php
namespace Coyote\Repositories\Redis;

use Coyote\Session;
use Illuminate\Container\Container;
use Illuminate\Support;

class SessionRepository
{
    private const string REDIS_KEY = 'sessions';

    /**
     * @var mixed
     */
    private $redis;

    public function __construct(Container $app)
    {
        $this->redis = $app['redis'];
    }

    public function set(string $sessionId, array $payload)
    {
        return $this->redis->hset(self::REDIS_KEY, $sessionId, \serialize($payload));
    }

    public function get(string $sessionId)
    {
        return $this->redis->hget(self::REDIS_KEY, $sessionId);
    }

    public function destroy(string $sessionId): void
    {
        $this->redis->hdel(self::REDIS_KEY, $sessionId);
    }

    public function all(): Support\Collection
    {
        return collect($this->redis->hvals(self::REDIS_KEY))
            ->map(fn($item) => new Session(\unserialize($item)));
    }

    public function gc(int $lifetime): true
    {
        foreach ($this->all() as $item) {
            if ($item->expired($lifetime)) {
                $this->redis->hdel(self::REDIS_KEY, $item['id']);
            }
        }
        return true;
    }
}
