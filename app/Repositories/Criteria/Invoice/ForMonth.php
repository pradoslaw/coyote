<?php

namespace Coyote\Repositories\Criteria\Invoice;

use Carbon\Carbon;
use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Coyote\Repositories\Criteria\Criteria;

class ForMonth extends Criteria
{
    /**
     * @var int
     */
    protected $year;

    /**
     * @var int
     */
    protected $month;

    /**
     * @param Carbon $date
     */
    public function __construct(Carbon $date)
    {
        $this->year = $date->year;
        $this->month = $date->month;
    }

    /**
     * @param mixed $model
     * @param Repository $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        return $model
            ->whereNotNull('number')
            ->whereRaw("extract(YEAR from created_at) = ?", [$this->year])
            ->whereRaw("extract(MONTH from created_at) = ?", [$this->month]);
    }
}
