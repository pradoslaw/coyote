<?php

namespace Boduch\Grid\Source;

use Boduch\Grid\Column;
use Boduch\Grid\Filters\FilterOperator;
use Boduch\Grid\Order;
use Illuminate\Database\Eloquent\Builder;

class EloquentSource implements SourceInterface
{
    /**
     * @var \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    protected $source;

    /**
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model $source
     */
    public function __construct($source)
    {
        $this->source = $source;
    }

    /**
     * @param Column[] $columns
     */
    public function applyFilters($columns)
    {
        foreach ($columns as $column) {
            /** @var \Boduch\Grid\Column $column */
            if ($column->isFilterable() && !$column->getFilter()->isEmpty()) {
                $this->buildQuery($column);
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
            ->when($order->getColumn(), function (Builder $builder) use ($order) {
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
     */
    protected function buildQuery(Column $column)
    {
        $name = $column->getFilter()->getName();
        $input = $column->getFilter()->getInput();

        if ($column->getFilter()->getOperator() == FilterOperator::OPERATOR_BETWEEN) {
            $this->source = $this->source->whereBetween($name, $input);
        } else {
            $this->source = $this->source->where(
                $name,
                $column->getFilter()->getOperator(),
                $this->normalizeValue($input, $column->getFilter()->getOperator()),
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
            $value = '%' . str_replace('*', '%', $value) . '%';
        }

        return $value;
    }
}
