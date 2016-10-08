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
     * @param array $location
     */
    public function __construct(array $location = [])
    {
        if ($location) {
            $this->latitude = $location['lat'];
            $this->longitude = $location['lng'];
        }
    }
}
