<?php

namespace Coyote\Services\Geocoder;

class Location
{
    /**
     * @var float
     */
    public $latitude;

    /**
     * @var float
     */
    public $longitude;

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
}
