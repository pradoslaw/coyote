<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\UserRepositoryInterface;

class SessionRepository extends Repository implements UserRepositoryInterface
{
    public function model()
    {
        return 'Coyote\Session';
    }
}
