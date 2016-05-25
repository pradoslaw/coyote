<?php

namespace Coyote\Repositories\Contracts;

/**
 * @package Coyote\Repositories\Contracts
 * @method $this withTrashed()
 */
interface WikiRepositoryInterface extends RepositoryInterface
{
    /**
     * @param string $path
     * @return mixed
     */
    public function findByPath($path);

    /**
     * @param int $pathId
     * @return mixed
     */
    public function findByPathId($pathId);

    /**
     * Get children articles of given parent_id.
     *
     * @param int|null $parentId
     * @return mixed
     */
    public function children($parentId = null);

    /**
     * @param int $pathId
     * @return mixed
     */
    public function parents($pathId);

    /**
     * @return mixed
     */
    public function treeList();

    /**
     * @param int $userId
     * @return mixed
     */
    public function getSubscribed($userId);
}
