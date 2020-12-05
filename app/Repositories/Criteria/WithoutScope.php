<?php

namespace Coyote\Repositories\Criteria;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;

class WithoutScope extends Criteria
{
    private string $scope;

    public function __construct(string $scope)
    {
        $this->scope = $scope;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @param Repository $repository
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply($model, Repository $repository)
    {
        return $model->withoutGlobalScope($this->scope);
    }
}
