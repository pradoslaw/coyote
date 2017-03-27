<?php

namespace Coyote\Repositories\Contracts;

interface SessionRepositoryInterface
{
    /**
     * @param string $sessionId
     * @param array $payload
     */
    public function set(string $sessionId, array $payload);

    /**
     * @param string $sessionId
     */
    public function destroy(string $sessionId);

    /**
     * @param string $sessionId
     * @return mixed
     */
    public function get(string $sessionId);

    /**
     * @return \Coyote\Session[]
     */
    public function all();

    /**
     * @param string|null $path
     * @return \Coyote\Session[]
     */
    public function getByPath($path = null);

    /**
     * @param int $lifetime
     * @return bool
     */
    public function gc(int $lifetime);
}
