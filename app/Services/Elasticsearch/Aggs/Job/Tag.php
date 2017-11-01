<?php

namespace Coyote\Services\Elasticsearch\Aggs\Job;

use Coyote\Services\Elasticsearch\DslInterface;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;

class Tag implements DslInterface
{
    use GlobalAggregationTrait;

    /**
     * Field required by GlobalAggregationTrait
     *
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $include = [];

    /**
     * @param array $include
     */
    public function __construct(array $include = [])
    {
        $this->name = 'tags';
        $this->include = $include;
    }

    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return array
     */
    public function apply(QueryBuilderInterface $queryBuilder)
    {
        $body = $queryBuilder->getBody();

        $body['aggs'][$this->name] = [
            'terms' => [
                'field'     => 'tags.original',
                'size'      => 10,
                'include'   => $this->include
            ]
        ];

        return $this->wrapGlobal($body);
    }
}
