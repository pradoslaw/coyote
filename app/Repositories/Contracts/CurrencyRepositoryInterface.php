<?php

namespace Coyote\Repositories\Contracts;

interface CurrencyRepositoryInterface extends RepositoryInterface
{
    /**
     * @param string $currency
     * @return float
     */
    public function yesterdaysRate($currency);
}
