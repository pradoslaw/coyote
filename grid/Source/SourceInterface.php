<?php

namespace Boduch\Grid\Source;

use Boduch\Grid\Column;
use Boduch\Grid\Order;

interface SourceInterface
{
    /**
     * @param Column[] $columns
     */
    public function applyFilters($columns);

    public function execute(?int $perPage, ?int $currentPage, Order $order);

    public function total(): int;
}
