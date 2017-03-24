<?php

namespace Coyote\Repositories\Contracts;

interface SessionRepositoryInterface
{
    /**
     * Remove old sessions from session_log table.
     */
//    public function purge();

    /**
     * @param string|null $path
     * @return \Illuminate\Support\Collection|static
     */
    public function getByPath($path = null);
}
