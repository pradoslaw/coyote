<?php

namespace Coyote\Repositories\Contracts;

interface JobRepositoryInterface extends RepositoryInterface
{
    /**
     * @param int $id
     * @return mixed
     */
    public function findById($id);

    /**
     * @return int
     */
    public function count();

    /**
     * Get subscribed job offers for given user id
     *
     * @param int $userId
     * @return mixed
     */
    public function subscribes($userId);
}
