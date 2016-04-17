<?php

namespace Coyote\Repositories\Criteria\Microblog;

use Coyote\Tag;
use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Coyote\Repositories\Contracts\RepositoryInterface;
use Coyote\Repositories\Criteria\Criteria;

class WithTag extends Criteria
{
    /**
     * @var string
     */
    private $tag;

    /**
     * WithTag constructor.
     * @param string $tag
     */
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
        $query = $model->whereIn('microblogs.id', function ($sub) {
            $sub->selectRaw('(CASE WHEN parent_id IS NOT NULL THEN parent_id ELSE microblogs.id END)')
                ->from((new Tag())->getTable())
                ->join('microblog_tags', 'microblog_tags.tag_id', '=', 'tags.id')
                ->join('microblogs', 'microblogs.id', '=', 'microblog_tags.microblog_id')
                ->where('name', $this->tag);
        });

        return $query;
    }
}
