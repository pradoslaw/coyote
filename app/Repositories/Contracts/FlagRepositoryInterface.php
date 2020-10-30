<?php

namespace Coyote\Repositories\Contracts;

interface FlagRepositoryInterface extends RepositoryInterface
{
    public function findAllByModel(string $model, array $ids);
    public function deleteByModel(string $model, int $value, int $userId);
}
