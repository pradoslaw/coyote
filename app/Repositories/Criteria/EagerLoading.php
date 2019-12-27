<?php

namespace Coyote\Repositories\Criteria;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;

class EagerLoading extends Criteria
{
    /**
     * @var string|string[]|array
     */
    private $relations;

    /**
     * @param string|string[]|array $relations
     */
    public function __construct($relations)
    {
        $this->relations = $relations;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @param Repository $repository
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply($model, Repository $repository)
    {
        return $model->with($this->relations);
    }
}
