<?php

namespace Boduch\Grid\Source;

use Boduch\Grid\Order;
use Illuminate\Http\Request;
use Boduch\Grid\Column;

interface SourceInterface
{
    /**
     * @param Column[] $columns
     * @param Request $request
     */
    public function applyFilters($columns, Request $request);

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
