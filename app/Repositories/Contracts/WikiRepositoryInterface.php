<?php

namespace Coyote\Repositories\Contracts;

use Illuminate\Http\Request;

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

    /**
     * @param \Coyote\Wiki $wiki
     * @param Request $request
     */
    public function save($wiki, Request $request);

    /**
     * @param int $id
     * @return mixed
     */
    public function delete($id);

    /**
     * @param int $id
     * @return mixed
     */
    public function restore($id);
}
