<?php

namespace Coyote\Services\Grid\Source;

use Coyote\Services\Grid\Order;

interface SourceInterface
{
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
