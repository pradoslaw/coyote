<?php

namespace Coyote\Repositories\Contracts;

interface JobRepositoryInterface extends RepositoryInterface
{
    /**
     * @param int $id
     * @return mixed
     */
    public function findById($id);
}
