<?php

namespace Coyote\Services\Invoice;

use Coyote\Country;
use Coyote\Coupon;
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
    public $discount;

    /**
     * @var Coupon|null
     */
    protected $coupon;

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
     * @param Coupon|null $coupon
     * @return $this
     */
    public function setCoupon(Coupon $coupon = null): Calculator
    {
        $this->coupon = $coupon;

        return $this;
    }

    public function setCountry(Country $country): Calculator
    {
        $this->vatRate = $country->vat_rate;

        return $this;
    }

    /**
     * @return float
     */
    public function netPrice()
    {
        return round($this->calculateDiscount($this->price), 2);
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

    /**
     * @param float $price
     * @return float
     */
    private function calculateDiscount($price)
    {
        return max(0, ($this->discount > 0 ? ($price - ($price * $this->discount)) : $price) - ($this->coupon !== null ? $this->coupon->amount : 0));
    }
}
