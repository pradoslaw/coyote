<?php

namespace Coyote\Services\Elasticsearch\Filters\Job;

use Coyote\Services\Elasticsearch\DslInterface;
use Coyote\Services\Elasticsearch\Filter;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;

class Location extends Filter implements DslInterface
{
    /**
     * @var array
     */
    protected $locations = [];

    /**
     * @param array $locations
     */
    public function __construct(array $locations = [])
    {
        $this->setLocations($locations);
    }

    /**
     * @param array $locations
     */
    public function setLocations(array $locations)
    {
        $this->locations = $locations;
    }

    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return mixed
     */
    public function apply(QueryBuilderInterface $queryBuilder)
    {
        if (empty($this->locations)) {
            return $queryBuilder->getBody();
        }

        $geodistance = [];

        foreach ($this->locations as $location) {
            $geodistance[] = [
                'geo_distance' => [
                    'distance' => '40km',
                    'coordinates' => $location
                ]
            ];
        }

        return $this->addOrFilter($queryBuilder, [
            'nested' => [
                'path' => 'locations',
                'query' => [
                    'filtered' => [
                        'query' => [
                            'match_all' => []
                        ],
                        'filter' => [
                            'and' => [
                                'filters' => [
                                    [
                                        'or' => [
                                            'filters' => $geodistance
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }
}
