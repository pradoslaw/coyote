<?php

namespace Coyote\Job;

use Coyote\Services\Geocoder\GeocoderInterface;
use Coyote\Services\Helper\City;

/**
 * @property array $locations
 * @property string $city
 * @property string[] $tags
 * @property bool $is_remote
 * @property int $currency_id
 */
class Preferences
{
    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @param string $data
     */
    public function __construct(string $data = null)
    {
        if ($data !== null) {
            $this->attributes = json_decode($data, true);
        }
    }

    /**
     * @param array $attributes
     * @return Preferences
     */
    public static function make(array $attributes = [])
    {
        $object = (new static);

        foreach ($attributes as $key => $value) {
            $object->{$key} = $value;
        }

        return $object->geocode();
    }

    /**
     * @param string $name
     */
    public function addTag($name)
    {
        if (!isset($this->attributes['tags'])) {
            $this->attributes['tags'] = [];
        }

        array_push($this->attributes['tags'], $name);
    }

    /**
     * @return $this
     */
    public function geocode()
    {
        if (!empty($this->city)) {
            $helper = new City();
            $input = $helper->grab($this->city);

            $geocoder = $this->getGeocoder();
            $locations = [];

            foreach ($input as $name) {
                $result = $geocoder->geocode($name);

                array_push($locations, ['lat' => $result->latitude, 'lon' => $result->longitude]);
            }

            $this->locations = $locations;
        }

        return $this;
    }

    /**
     * Return TRUE is preferences are empty. For example - user might fill fields with empty values.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->attributes) ||
            (empty($this->attributes['tags'])
                && empty($this->attributes['city']
                    && empty($this->attributes['salary'])));
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->attributes[$name]);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->attributes);
    }

    /**
     * @return GeocoderInterface
     */
    protected function getGeocoder(): GeocoderInterface
    {
        return app(GeocoderInterface::class);
    }
}
