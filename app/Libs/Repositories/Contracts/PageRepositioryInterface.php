<?php

namespace Coyote\Repositories\Contracts;

interface PageRepositoryInterface extends RepositoryInterface
{
    /**
     * @param string $path
     * @return mixed
     */
    public function findByPath($path);
}
