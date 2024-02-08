<?php

namespace Boduch\Grid\Source;

use Boduch\Grid\Order;
use Boduch\Grid\Column;

interface SourceInterface
{
    /**
     * @param Column[] $columns
     */
    public function applyFilters($columns);

    /**
     * @param int $perPage
     * @param int $currentPage
     * @param Order $order
     * @return mixed
     */
    public function execute($perPage, $currentPage, Order $order);

    /**
     * @return int
     */
    public function total();
}
