<?php

namespace Coyote\Services\Session;

use Illuminate\Container\Container;
use Illuminate\Session\CacheBasedSessionHandler;
use Illuminate\Contracts\Auth\Guard;
use Jenssegers\Agent\Agent;

class Handler extends CacheBasedSessionHandler
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @param Container $container
     * @return $this
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function read($sessionId)
    {
        return parent::read($this->sessionId($sessionId));
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $data)
    {
        $data = unserialize($data);
        $data = $this->getDefaultPayload($data);

        return parent::write($this->sessionId($sessionId), serialize($data));
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId)
    {
        return parent::destroy($this->sessionId($sessionId));
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

    /**
     * @param string $sessionId
     * @return string
     */
    private function sessionId($sessionId)
    {
        return 'session:' . $sessionId;
    }
}
