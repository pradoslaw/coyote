<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\SessionRepositoryInterface;

class SessionRepository extends Repository implements SessionRepositoryInterface
{
    public function model()
    {
        return 'Coyote\Session';
    }
}
