<?php

namespace Boduch\Grid\Source;

use Boduch\Grid\Column;
use Boduch\Grid\Filters\FilterOperator;
use Boduch\Grid\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CollectionSource implements SourceInterface
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @param Collection $source
     */
    public function __construct($source)
    {
        $this->collection = $source;
    }

    /**
     * @param Column[] $columns
     * @param Request $request
     */
    public function applyFilters($columns, Request $request)
    {
        foreach ($columns as $column) {
            if ($column->isFilterable() && $request->has($column->getName())) {
                $this->filterValue(
                    $column->getName(),
                    $request->input($column->getName()),
                    $column->getFilter()->getOperator()
                );
            }
        }
    }

    /**
     * @param int $perPage
     * @param int $currentPage
     * @param Order $order
     * @return Collection
     */
    public function execute($perPage, $currentPage, Order $order)
    {
        if ($order->getDirection()) {
            $direction = $order->getDirection() == 'desc' ? 'sortByDesc' : 'sortBy';
            $this->collection = $this->collection->$direction($order->getColumn());
        }

        return $this
            ->collection
            ->forPage($currentPage, $perPage);
    }

    /**
     * @return int
     */
    public function total()
    {
        return $this->collection->count();
    }

    /**
     * @param string $key
     * @param string $value
     * @param string $operator
     * @return mixed
     */
    protected function filterValue($key, $value, $operator)
    {
        if ($operator == FilterOperator::OPERATOR_LIKE || $operator == FilterOperator::OPERATOR_ILIKE) {
            $this->collection = $this->collection->whereLoose($key, $value);
        } else {
            $this->collection = $this->collection->where($key, $value);
        }
    }
}
