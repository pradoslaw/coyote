<?php

namespace Coyote\Services\Session;

use Coyote\Repositories\Contracts\SessionRepositoryInterface as SessionRepository;
use Illuminate\Container\Container;
use Illuminate\Contracts\Auth\Guard;
use Jenssegers\Agent\Agent;

class Handler implements \SessionHandlerInterface
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var SessionRepository
     */
    protected $repository;

    /**
     * @param SessionRepository $repository
     * @param Container $container
     */
    public function __construct(SessionRepository $repository, Container $container)
    {
        $this->repository = $repository;
        $this->container = $container;
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
        return $this->repository->get($sessionId);
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $payload)
    {
        $payload = unserialize($payload);
        $payload = ['id' => $sessionId] + $this->getDefaultPayload($payload);

        $this->repository->set($sessionId, $payload);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId)
    {
        $this->repository->destroy($sessionId);

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
            /** @var \Illuminate\Http\Request $request */
            $request = $container->make('request');

            $payload['ip'] = $request->ip();
            $payload['browser'] = substr((string) $request->header('User-Agent'), 0, 500);
            $payload['robot'] = $this->robot($payload['browser']);

            if (!$request->ajax()) {
                $path = str_limit($request->path(), 999, '');

                // we can't save "url" in session because laravel saves previous URL as url.intended
                $payload['path'] = $path === '/' ? $path : ('/' . $path);
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
