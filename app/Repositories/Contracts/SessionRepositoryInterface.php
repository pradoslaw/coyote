<?php

namespace Coyote\Repositories\Contracts;

interface SessionRepositoryInterface
{
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
