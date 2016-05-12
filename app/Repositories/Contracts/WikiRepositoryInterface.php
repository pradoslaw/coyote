<?php

namespace Coyote\Repositories\Contracts;

/**
 * @package Coyote\Repositories\Contracts
 * @method $this withTrashed()
 */
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

    /**
     * @return mixed
     */
    public function treeList();
}
