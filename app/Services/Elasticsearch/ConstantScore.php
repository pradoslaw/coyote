<?php

namespace Coyote\Services\Elasticsearch;

class ConstantScore implements DslInterface
{
    /**
     * @var DslInterface
     */
    protected $filter;

    /**
     * @var int
     */
    protected $boost;

    /**
     * @param DslInterface $filter
     * @param int $boost
     */
    public function __construct(DslInterface $filter, $boost)
    {
        $this->filter = $filter;
        $this->boost = $boost;
    }

    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return array
     */
    public function apply(QueryBuilderInterface $queryBuilder)
    {
        return [
            'constant_score' => [
                'filter' => $this->filter->apply($queryBuilder),
                'boost' => $this->boost
            ]
        ];
    }
}
