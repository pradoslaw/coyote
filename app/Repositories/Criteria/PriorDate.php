<?php

namespace Coyote\Repositories\Criteria;

use Carbon\Carbon;
use Coyote\Repositories\Contracts\RepositoryInterface as Repository;

class PriorDate extends Criteria
{
    /**
     * @var Carbon
     */
    private $date;

    /**
     * @param Carbon $date
     */
    public function __construct(Carbon $date)
    {
        $this->date = $date;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @param Repository $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        return $model->where('created_at', '>=', $this->date);
    }
}
