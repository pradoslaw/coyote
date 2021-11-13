<?php

namespace Coyote\Repositories\Criteria;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Coyote\Topic;
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
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param Repository $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        return $model->whereIn($model->getTable() . '.id', function (Builder $builder) use ($model) {
            return $builder
                ->select('resource_id')
                ->from('tags')
                ->join('tag_resources', 'tag_resources.tag_id', '=', 'tags.id')
                ->where('tag_resources.resource_type', '=', get_class($model))
                ->whereIn('name', $this->tags);
        });
    }
}
