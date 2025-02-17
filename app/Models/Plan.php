<?php
namespace Coyote;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property float $price
 * @property float $gross_price
 * @property float $vat_rate
 * @property int $currency_id
 * @property bool $is_active
 * @property int $discount
 * @property int $length
 * @property array $benefits
 * @property int $is_default
 * @property int $boost
 */
class Plan extends Model
{
    public $timestamps = false;
    protected $casts = ['benefits' => 'json', 'price' => 'float', 'vat_rate' => 'float'];

    public function currency(): HasOne
    {
        return $this->hasOne(Currency::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getGrossPriceAttribute()
    {
        return $this->price * $this->vat_rate;
    }
}
