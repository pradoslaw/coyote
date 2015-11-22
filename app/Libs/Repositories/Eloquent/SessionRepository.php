<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\RepositoryInterface;

class SessionRepository extends Repository implements RepositoryInterface
{
    public function model()
    {
        return 'Coyote\Session';
    }
}
