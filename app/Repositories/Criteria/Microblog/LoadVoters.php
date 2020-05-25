<?php

namespace Coyote\Repositories\Criteria\Microblog;

use Coyote\Repositories\Criteria\Criteria;
use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Illuminate\Database\Eloquent\Model;

class LoadVoters extends Criteria
{
    /**
     * @param Model $model
     * @param Repository $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        return $model->select('microblogs.*')->includeVoters();
    }
}
