<?php

namespace Coyote\Repositories\Contracts;

interface TagRepositoryInterface extends RepositoryInterface
{
    /**
     * @param $name
     * @return mixed
     */
    public function lookupName($name);
}
