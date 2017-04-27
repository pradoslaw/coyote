<?php

namespace Coyote\Services\Invoice;

use Illuminate\Contracts\Support\Arrayable;

class Calculator implements Arrayable
{
    /**
     * @var float
     */
    public $price;

    /**
     * @var float
     */
    public $vatRate;

    /**
     * @var float
     */
    public $quantity;

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $camelCase = camel_case($key);

            if (property_exists($this, $camelCase)) {
                $this->{$camelCase} = $value;
            }
        }
    }

    /**
     * @return float
     */
    public function netPrice()
    {
        return round($this->price * $this->quantity, 2);
    }

    /**
     * @return float
     */
    public function grossPrice()
    {
        return round($this->netPrice() * $this->vatRate, 2);
    }

    /**
     * @return float
     */
    public function vatPrice()
    {
        return round($this->grossPrice() - $this->netPrice(), 2);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'vat_rate'      => $this->vatRate,
            'net_price'     => $this->netPrice(),
            'gross_price'   => $this->grossPrice(),
            'vat_price'     => $this->vatPrice()
        ];
    }
}
