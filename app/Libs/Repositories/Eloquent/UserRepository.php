<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\UserRepositoryInterface;

class UserRepository extends Repository implements UserRepositoryInterface
{
    public function model()
    {
        return 'Coyote\User';
    }

    /**
     * @inheritdoc
     */
    public function findByName($name)
    {
        return $this->model->select(['id', 'name', 'photo'])->where('name', 'ILIKE', $name . '%')->get();
    }
}
