<?php

namespace Coyote\Services\Elasticsearch\Filters\Job;

use Coyote\Services\Elasticsearch\DslInterface;
use Coyote\Services\Elasticsearch\Filter;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;
use Coyote\Services\Parser\Helpers;
use Illuminate\Support\Str;

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
            $city = (new Helpers\City())->grab($city);
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
            return (object) [];
        }

        return [
            'nested' => [
                'path' => 'locations',
                'query' => [
                    'match' => [
                        'locations.label' => implode(' ', $this->cities)
                    ]
                ]
            ]
        ];
    }
}
