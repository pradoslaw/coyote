<?php

namespace Coyote\Services\Grid\Source;

use Coyote\Services\Grid\Order;

interface SourceInterface
{
    /**
     * @param int $perPage
     * @param Order $order
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function execute($perPage, Order $order);
}
