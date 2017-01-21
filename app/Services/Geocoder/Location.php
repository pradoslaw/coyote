<?php

namespace Coyote\Services\Geocoder;

use Coyote\Services\Arrayable\ToArray;
use Illuminate\Contracts\Support\Arrayable;

class Location implements Arrayable
{
    use ToArray;

    /**
     * @var float
     */
    public $latitude;

    /**
     * @var float
     */
    public $longitude;

    /**
     * @var string
     */
    public $city;

    /**
     * Location constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->latitude !== null && $this->longitude !== null && $this->city !== null;
    }
}
