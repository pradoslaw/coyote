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

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        return $this->repository->get($id);
    }

    public function write(string $id, string $data): bool
    {
        $payload = unserialize($data);
        $payload = ['id' => $id] + $this->getDefaultPayload($payload);
        $this->repository->set($id, $payload);
        return true;
    }

    public function destroy(string $id): bool
    {
        $this->repository->destroy($id);
        return true;
    }

    public function gc(int $max_lifetime): int
    {
        return 1;
    }

    /**
     * Get the default payload for the session.
     *
     * @param array $payload
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
            $payload['browser'] = substr((string)$request->header('User-Agent'), 0, 500);
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
