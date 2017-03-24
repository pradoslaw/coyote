<?php

namespace Coyote\Services\Session;

use Illuminate\Container\Container;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Redis\Database as Redis;
use Jenssegers\Agent\Agent;

class Handler implements \SessionHandlerInterface
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Redis
     */
    protected $redis;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->redis = $container['redis'];
    }

    /**
     * {@inheritdoc}
     */
    public function open($savePath, $sessionName)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($sessionId)
    {
        return $this->redis->get($sessionId);
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $payload)
    {
        $payload = unserialize($payload);
        $payload = ['id' => $sessionId] + $this->getDefaultPayload($payload);

        $this->redis->set($sessionId, serialize($payload));
        $this->redis->sadd('sessions', $sessionId);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId)
    {
        $this->redis->srem('sessions', $sessionId);
        $this->redis->del($sessionId);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function gc($lifetime)
    {
        return true;
    }

    /**
     * Get the default payload for the session.
     *
     * @param  array  $payload
     * @return array
     */
    protected function getDefaultPayload($payload)
    {
        $payload['updated_at'] = time();

        if (!isset($payload['created_at'])) {
            $payload['created_at'] = time();
        }

        if (!$container = $this->container) {
            return $payload;
        }

        if ($container->bound(Guard::class)) {
            $payload['user_id'] = $container->make(Guard::class)->id();
        }

        if ($container->bound('request')) {
            $request = $container->make('request');

            $payload['ip'] = $request->ip();
            $payload['browser'] = substr((string) $request->header('User-Agent'), 0, 500);
            $payload['robot'] = $this->robot($payload['browser']);

            if (!$request->ajax()) {
                $payload['url'] = str_limit($request->fullUrl(), 3999);
            }
        }

        return $payload;
    }

    /**
     * @param string $browser
     * @return null|string
     */
    private function robot($browser)
    {
        $agent = new Agent();

        if (!$agent->isRobot($browser)) {
            return '';
        }

        return $agent->robot();
    }
}
