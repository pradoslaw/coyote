<?php

namespace Coyote\Repositories\Criteria\Topic;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Coyote\Repositories\Criteria\Criteria;
use Coyote\Tag;
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
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @param Repository $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        return $model
            ->join('tag_resources', 'tag_resources.resource_id', '=', 'topics.id')
            ->join('tags', 'tags.id', '=', 'tag_id')
            ->where('tag_resources.resource_type', '=', Topic::class)
            ->whereIn('name', $this->tags);
    }
}
