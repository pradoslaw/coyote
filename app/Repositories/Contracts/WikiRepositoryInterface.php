<?php

namespace Coyote\Repositories\Contracts;

interface WikiRepositoryInterface extends RepositoryInterface
{
    /**
     * Get children articles of given parent_id.
     *
     * @param int|null $parentId
     * @param int $depth
     * @return mixed
     */
    public function children($parentId = null, $depth = 10);

    /**
     * @param int $id
     * @return mixed
     */
    public function parents($id);
}
