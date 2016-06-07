<?php

namespace Coyote\Services\Grid\Source;

interface SourceInterface
{
    /**
     * @param string $column
     * @param string $direction
     * @return $this
     */
    public function orderBy($column, $direction);
    
    /**
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage);
}
