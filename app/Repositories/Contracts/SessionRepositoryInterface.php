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
     * @return mixed
     */
    public function destroy(string $sessionId);

    /**
     * Return session as serialized string (required by laravel's session)
     *
     * @param string $sessionId
     * @return mixed
     */
    public function get(string $sessionId);

    /**
     * @return \Coyote\Session[]
     */
    public function all();

    /**
     * @param int $lifetime
     * @return bool
     */
    public function gc(int $lifetime);
}
