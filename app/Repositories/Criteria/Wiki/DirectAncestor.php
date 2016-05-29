<?php

namespace Coyote\Repositories\Criteria\Wiki;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Coyote\Repositories\Criteria\Criteria;

class DirectAncestor extends Criteria
{
    /**
     * @var int
     */
    protected $parentId;

    /**
     * @param int $parentId
     */
    public function __construct($parentId)
    {
        $this->parentId = $parentId;
    }

    /**
     * @param \Coyote\Wiki $model
     * @param Repository $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        return $model->where('parent_id', $this->parentId);
    }
}
