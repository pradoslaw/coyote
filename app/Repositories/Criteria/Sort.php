<?php

namespace Coyote\Repositories\Criteria;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;

class Sort extends Criteria
{
    public const ASC = 'asc';
    public const DESC = 'desc';

    /**
     * @var string
     */
    private $sort;

    /**
     * @var string
     */
    private $order;

    /**
     * @param $sort
     * @param string $order
     */
    public function __construct($sort, $order = self::DESC)
    {
        $this->sort = $sort;
        $this->order = $order;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @param Repository $repository
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply($model, Repository $repository)
    {
        return $model->orderBy($this->sort, $this->order);
    }
}
