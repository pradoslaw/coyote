<?php

namespace Coyote\Services\Geocoder;

interface GeocoderInterface
{
    /**
     * @param string $address
     * @return Location
     */
    public function geocode(string $address);
}
