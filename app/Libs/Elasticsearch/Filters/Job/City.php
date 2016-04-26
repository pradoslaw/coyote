<?php

namespace Coyote\Elasticsearch\Filters\Job;

use Coyote\Elasticsearch\DslInterface;
use Coyote\Elasticsearch\Filter;
use Coyote\Elasticsearch\QueryBuilderInterface;
use Coyote\Services\Parser\Reference;

class City extends Filter implements DslInterface
{
    /**
     * @var array
     */
    protected $cities = [];

    /**
     * City constructor.
     * @param $cities
     */
    public function __construct($cities = [])
    {
        $this->setCities($cities);
    }

    /**
     * @param $city
     */
    public function addCity($city)
    {
        if (is_array($city)) {
            foreach ($city as $value) {
                $this->addCity($value);
            }
        } else {
            $city = (new Reference\City())->grab($city);
            $this->cities = array_merge($this->cities, $city);
        }
    }

    /**
     * @param $cities
     */
    public function setCities($cities)
    {
        if (!is_array($cities)) {
            $cities = [$cities];
        }

        $this->cities = $cities;
    }

    /**
     * @return array
     */
    public function getCities()
    {
        return $this->cities;
    }

    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return mixed
     */
    public function apply(QueryBuilderInterface $queryBuilder)
    {
        if (empty($this->cities)) {
            return $queryBuilder->getBody();
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
                            'terms' => [
                                'city_original' => array_map('mb_strtolower', $this->cities)
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }
}
