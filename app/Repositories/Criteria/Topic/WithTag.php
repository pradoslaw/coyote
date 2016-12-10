<?php

namespace Coyote\Repositories\Criteria\Topic;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Coyote\Repositories\Criteria\Criteria;
use Coyote\Tag;

class WithTag extends Criteria
{
    private $tag;

    /**
     * @param string $tag
     */
    public function __construct($tag)
    {
        $this->tag = $tag;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @param Repository $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        $subSql = Tag::select(['topic_id'])
            ->join('topic_tags', 'topic_tags.tag_id', '=', 'tags.id')
            ->whereRaw('name = \'' . $this->tag . '\'');

        $query = $model->whereRaw('topics.id IN(' . $subSql->toSql() . ')');
        return $query;
    }
}
