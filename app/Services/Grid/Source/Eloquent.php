<?php

namespace Coyote\Services\Grid\Source;

class Eloquent implements SourceInterface
{
    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $source;

    /**
     * @param \Illuminate\Database\Eloquent\Builder $source
     */
    public function __construct($source)
    {
        $this->source = $source;
    }

    /**
     * @param string $column
     * @param string $direction
     * @return $this
     */
    public function orderBy($column, $direction)
    {
        $this->source->orderBy($column, $direction);

        return $this;
    }

    /**
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage)
    {
        return $this->source->paginate($perPage);
    }
}
