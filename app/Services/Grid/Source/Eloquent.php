<?php

namespace Coyote\Services\Grid\Source;

use Coyote\Services\Grid\Column;
use Coyote\Services\Grid\Filters\FilterOperation;
use Coyote\Services\Grid\Order;
use Illuminate\Http\Request;

class Eloquent implements SourceInterface
{
    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $source;

    /**
     * @var int
     */
    protected $total;

    /**
     * @param \Illuminate\Database\Eloquent\Builder $source
     */
    public function __construct($source)
    {
        $this->source = $source;
    }

    /**
     * @param Column[] $columns
     * @param Request $request
     */
    public function setFiltersData($columns, Request $request)
    {
        foreach ($columns as $column) {
            if ($column->isFilterable() && $request->has($column->getName())) {
                $this->source->where(
                    $column->getName(),
                    $column->getFilter()->getOperator(),
                    $this->normalizeValue($request->input($column->getName()), $column->getFilter()->getOperator())
                );
            }
        }
    }

    /**
     * @param int $perPage
     * @param int $currentPage
     * @param Order $order
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function execute($perPage, $currentPage, Order $order)
    {
        $this->total = $this->source->count();

        return $this
            ->source
            ->orderBy($order->getColumn(), $order->getDirection())
            ->skip(($currentPage - 1) * $perPage)
            ->take($perPage)
            ->get();
    }

    /**
     * @return int
     */
    public function total()
    {
        return $this->total;
    }

    /**
     * @param string $value
     * @param string $operator
     * @return mixed
     */
    protected function normalizeValue($value, $operator)
    {
        if ($operator == FilterOperation::OPERATOR_LIKE || $operator == FilterOperation::OPERATOR_ILIKE) {
            $value = str_replace('*', '%', $value);
        }

        return $value;
    }
}
