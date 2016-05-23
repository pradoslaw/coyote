<?php

namespace Coyote\Services\Elasticsearch;

class Geodistance implements DslInterface
{
    /**
     * @var float
     */
    protected $latitude;

    /**
     * @var float
     */
    protected $longitude;

    /**
     * @param float $latitude
     * @param float $longitude
     */
    public function __construct($latitude, $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return array
     */
    public function apply(QueryBuilderInterface $queryBuilder)
    {
        $body = $queryBuilder->getBody();
        $body['sort'][] = [
            '_geo_distance' => [
                'locations.coordinates' => [
                    'lat' => $this->latitude,
                    'lon' => $this->longitude
                ],
                'order' => 'asc',
                'unit' => 'km',
                'distance_type' => 'plane'
            ]
        ];

        return $body;
    }
}
