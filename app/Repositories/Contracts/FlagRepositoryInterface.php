<?php

namespace Coyote\Repositories\Contracts;

interface FlagRepositoryInterface extends RepositoryInterface
{
    public function findAllByModel(string $model);
    public function deleteByModel(string $model, int $value, int $userId = null);
}
