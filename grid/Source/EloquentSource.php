<?php

namespace Boduch\Grid\Source;

use Boduch\Grid\Column;
use Boduch\Grid\Filters\Field;
use Boduch\Grid\Filters\FilterOperator;
use Boduch\Grid\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EloquentSource implements SourceInterface
{
    /**
     * @param Builder|Model $source
     */
    public function __construct(private $source)
    {
    }

    /**
     * @param Column[] $columns
     */
    public function applyFilters($columns): void
    {
        foreach ($columns as $column) {
            $field = $column->getFilter();
            if ($column->isFilterable() && !$field->isEmpty()) {
                $this->buildQuery($field);
            }
        }
    }

    public function execute(?int $perPage, ?int $currentPage, Order $order)
    {
        return $this
            ->source
            ->when($order->getColumn(), fn(Builder $builder) => $builder->orderBy($order->getColumn(), $order->getDirection()))
            ->forPage($currentPage, $perPage)
            ->get();
    }

    public function total(): int
    {
        return $this->source->count();
    }

    private function buildQuery(Field $field): void
    {
        $name = $field->getName();
        $input = $field->getInput();

        if ($field->getOperator() == FilterOperator::OPERATOR_BETWEEN) {
            $this->source = $this->source->whereBetween($name, $input);
        } else {
            $this->source = $this->source->where(
                $name,
                $field->getOperator(),
                $this->normalizeValue($input, $field->getOperator()),
            );
        }
    }

    private function normalizeValue(array|string $value, string $operator): array|string
    {
        if (\is_array($value)) {
            $value = \array_filter($value);
        }
        if ($operator == FilterOperator::OPERATOR_LIKE || $operator == FilterOperator::OPERATOR_ILIKE) {
            if (!\str_contains($value, '*')) {
                $value = '*' . $value . '*';
            }
            return \str_replace('*', '%', $value);
        }
        return $value;
    }
}
