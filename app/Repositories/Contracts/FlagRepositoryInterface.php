<?php

namespace Coyote\Repositories\Contracts;

interface FlagRepositoryInterface extends RepositoryInterface
{
    public function findAllByModel(string $model, array $ids);

    /**
     * @param $key
     * @param $value
     * @param int|null $userId
     */
    public function deleteBy($key, $value, $userId = null);
}
