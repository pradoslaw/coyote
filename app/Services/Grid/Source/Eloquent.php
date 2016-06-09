<?php

namespace Coyote\Services\Grid\Source;

use Coyote\Services\Grid\Order;

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
}
