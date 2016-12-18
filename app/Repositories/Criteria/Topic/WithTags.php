<?php

namespace Coyote\Repositories\Criteria\Topic;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Coyote\Repositories\Criteria\Criteria;
use Illuminate\Database\Query\Builder;

class WithTags extends Criteria
{
    /**
     * @var string[]
     */
    private $tags;

    /**
     * @param string|string[] $tags
     */
    public function __construct($tags)
    {
        if (!is_array($tags)) {
            $tags = [$tags];
        }

        $this->tags = $tags;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @param Repository $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        return $model->whereIn('topics.id', function (Builder $builder) {
            return $builder->select(['topic_id'])
                ->from('tags')
                ->join('topic_tags', 'topic_tags.tag_id', '=', 'tags.id')
                ->whereIn('name', $this->tags);
        });
    }
}
