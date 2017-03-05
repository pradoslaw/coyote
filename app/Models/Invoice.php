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
 * @property Item[] $items
 */
class Invoice extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['user_id', 'name', 'address', 'city', 'postal_code'];

    /**
     * @var array
     */
    protected $dates = ['created_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(Item::class);
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
     * Set the value of the "updated at" attribute.
     *
     * @param  mixed  $value
     * @return $this
     */
    public function setUpdatedAt($value)
    {
        return $this;
    }
}
