<?php

namespace Coyote\Repositories\Eloquent;

use Carbon\Carbon;
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
     * @param string $date
     * @param string $currency
     * @return float
     */
    public function rate($date, $currency)
    {
        return $this
            ->model
            ->where('name', $currency)
            ->join('exchanges', 'currency_id', '=', 'currencies.id')
            ->where('date', $date)
            ->value('value');
    }

    /**
     * @inheritdoc
     */
    public function yesterdaysRate($currency)
    {
        return $this->rate(Carbon::yesterday(), $currency);
    }
}
