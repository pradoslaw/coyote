<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Currency;
use Coyote\Repositories\Contracts\CurrencyRepositoryInterface;

class CurrencyRepository extends Repository implements CurrencyRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return Currency::class;
    }

//    public function addExchange()
}
