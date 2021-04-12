<?php

namespace Coyote\Repositories\Criteria\Tag;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Coyote\Repositories\Criteria\Criteria;

class ForCategory extends Criteria
{
    /**
     * @var int
     */
    protected $categoryId;

    /**
     * @param int $categoryId
     */
    public function __construct(int $categoryId)
    {
        $this->categoryId = $categoryId;
    }

    /**
     * @param mixed $model
     * @param Repository $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        return $model
            ->select('tags.*')
            ->where('category_id', $this->categoryId)
            ->whereNotNull('logo')
            ->orderByRaw("resources->>'Coyote\Job' DESC");
    }
}
