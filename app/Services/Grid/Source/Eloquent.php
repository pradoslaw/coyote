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
     * @param \Illuminate\Database\Eloquent\Builder $source
     */
    public function __construct($source)
    {
        $this->source = $source;
    }

    /**
     * @param int $perPage
     * @param Order $order
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function execute($perPage, Order $order)
    {
        return $this->source->orderBy($order->getColumn(), $order->getDirection())->paginate($perPage);
    }
}
