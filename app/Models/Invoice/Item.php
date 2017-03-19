<?php

namespace Coyote\Invoice;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $description
 * @property float $price
 * @property int $vat_rate
 */
class Item extends Model
{
    /**
     * @var string
     */
    protected $table = 'invoice_items';

    /**
     * @var array
     */
    protected $fillable = ['description', 'price', 'vat_rate'];

    /**
     * @var bool
     */
    public $timestamps = false;

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
    public function grossPrice()
    {
        return round($this->price * $this->vat_rate, 2);
    }
}
