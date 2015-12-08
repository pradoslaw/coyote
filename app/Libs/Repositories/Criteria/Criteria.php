<?php

namespace Coyote\Repositories\Criteria;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;

abstract class Criteria
{
    abstract public function apply($model, Repository $repository);
}
