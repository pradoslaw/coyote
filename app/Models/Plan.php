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
 * @property int $discount
 * @property int $length
 * @property array $benefits
 * @property bool $is_default
 */
class Plan extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $casts = ['benefits' => 'json'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function currency()
    {
        return $this->hasOne(Currency::class);
    }
}
