<?php

namespace Boduch\Grid\Source;

use Boduch\Grid\Column;
use Boduch\Grid\Filters\FilterOperator;
use Boduch\Grid\Order;
use Illuminate\Http\Request;

class EloquentDataSource implements SourceInterface
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
     * @param Column[] $columns
     * @param Request $request
     */
    public function applyFilters($columns, Request $request)
    {
        foreach ($columns as $column) {
            if ($column->isFilterable() && !$this->isEmpty($column->getName(), $request)) {
                $this->buildQuery($column, $request);
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
        return $this
            ->source
            ->when($order->getColumn(), function ($builder) use ($order) {
                return $builder->orderBy($order->getColumn(), $order->getDirection());
            })
            ->forPage($currentPage, $perPage)
            ->get();
    }

    /**
     * @return int
     */
    public function total()
    {
        return $this->source->count();
    }

    /**
     * @param Column $column
     * @param Request $request
     */
    protected function buildQuery(Column $column, Request $request)
    {
        if ($column->getFilter()->getOperator() == FilterOperator::OPERATOR_BETWEEN) {
            $this->source->whereBetween($column->getName(), $request->input($column->getName()));
        } else {
            $this->source->where(
                $column->getName(),
                $column->getFilter()->getOperator(),
                $this->normalizeValue($request->input($column->getName()), $column->getFilter()->getOperator())
            );
        }
    }

    /**
     * @param string|string[] $value
     * @param string $operator
     * @return mixed
     */
    protected function normalizeValue($value, $operator)
    {
        if (is_array($value)) {
            $value = array_filter($value);
        }

        if ($operator == FilterOperator::OPERATOR_LIKE || $operator == FilterOperator::OPERATOR_ILIKE) {
            $value = str_replace('*', '%', $value);
        }

        return $value;
    }

    /**
     * @param string $name
     * @param Request $request
     * @return bool
     */
    protected function isEmpty($name, Request $request)
    {
        if (is_array($request->input($name))) {
            return empty(array_filter($request->input($name)));
        } else {
            return !$request->has($name);
        }
    }
}
