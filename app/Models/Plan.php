<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property string $description
 * @property float $price
 * @property float $vat_rate
 * @property int $currency_id
 * @property bool $is_active
 */
class Plan extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function currency()
    {
        return $this->hasOne(Currency::class);
    }
}
