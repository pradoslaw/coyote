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
     * @return \Illuminate\Support\Collection
     */
    public function all();

    /**
     * @param string|null $path
     * @return \Illuminate\Support\Collection
     */
    public function getByPath($path = null);

    /**
     * @param int $lifetime
     * @return \Illuminate\Support\Collection
     */
    public function gc(int $lifetime);
}
