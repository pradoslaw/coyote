<?php

namespace Coyote;

use Coyote\Invoice\Item;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $user_id
 * @property string $name
 * @property string $number
 * @property string $address
 * @property string $city
 * @property string $postal_code
 * @property int $currency_id
 * @property int $country_id
 * @property Item[] $items
 * @property Currency $currency
 * @property Country $country
 * @property int $seq
 */
class Invoice extends Model
{
    const UPDATED_AT = null;

    /**
     * @var array
     */
    protected $fillable = ['user_id', 'name', 'number', 'vat_id', 'address', 'city', 'postal_code', 'currency_id', 'country_id'];

    /**
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    public static function boot()
    {
        parent::boot();

        static::saving(function (Invoice $model) {
            foreach (['number', 'country_id'] as $key) {
                if (empty($model->{$key})) {
                    $model->{$key} = null;
                }
            }
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(Item::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * @return float
     */
    public function netPrice()
    {
        $price = 0.0;

        foreach ($this->items as $item) {
            $price += $item->price;
        }

        return round($price, 2);
    }

    /**
     * @return float
     */
    public function grossPrice()
    {
        $price = 0.0;

        foreach ($this->items as $item) {
            $price += $item->grossPrice();
        }

        return round($price, 2);
    }

    /**
     * @return float
     */
    public function getGrossPriceAttribute()
    {
        return $this->grossPrice();
    }

    /**
     * @return float
     */
    public function getNetPriceAttribute()
    {
        return $this->netPrice();
    }
}
