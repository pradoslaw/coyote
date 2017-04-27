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

    /**
     * @inheritdoc
     */
    public function latest($currency)
    {
        return $this
            ->model
            ->where('name', $currency)
            ->join('exchanges', 'currency_id', '=', 'currencies.id')
            ->orderBy('exchanges.id', 'DESC')
            ->limit(1)
            ->value('value');
    }
}
