<?php

namespace Coyote\Repositories\Criteria\Microblog;

use Coyote\Microblog\Tag;
use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Coyote\Repositories\Contracts\RepositoryInterface;
use Coyote\Repositories\Criteria\Criteria;

class WithTag extends Criteria
{
    private $tag;

    public function __construct($tag)
    {
        $this->tag = $tag;
    }

    /**
     * @param $model
     * @param RepositoryInterface $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        $subSql = Tag::select(['microblog_id'])->whereRaw('name = \'' . $this->tag . '\'');

        $query = $model->whereRaw('microblogs.id IN(' . $subSql->toSql() . ')');
        return $query;
    }
}
