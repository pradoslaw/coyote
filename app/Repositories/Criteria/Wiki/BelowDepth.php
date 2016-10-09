<?php

namespace Coyote\Repositories\Criteria\Wiki;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Coyote\Repositories\Criteria\Criteria;

class BelowDepth extends Criteria
{
    /**
     * @param \Coyote\Wiki $model
     * @param Repository $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        return $model->where('depth', '<=', 2)->whereRaw('(SELECT COUNT(*) FROM wiki_paths WHERE parent_id = id) > 0');
    }
}
