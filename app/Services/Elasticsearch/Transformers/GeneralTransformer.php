<?php

namespace Coyote\Services\Elasticsearch\Transformers;

class GeneralTransformer implements \IteratorAggregate
{
    /**
     * @var int
     */
    protected $total = 0;

    /**
     * @var array
     */
    protected $hits = [];

    /**
     * @var int
     */
    protected $took;

    /**
     * Response constructor.
     * @param array $response
     */
    public function __construct($response)
    {
        if (isset($response['hits'])) {
            $this->total = $response['hits']['total'];
            $this->took = $response['took'];
            $this->transform($response['hits']['hits']);
        }
    }

    /**
     * Total Hits
     *
     * @return int
     */
    public function total()
    {
        return $this->total;
    }

    /**
     * @return int
     */
    public function took()
    {
        return $this->took;
    }

    /**
     * @param array $hits
     */
    protected function transform(array $hits)
    {
        foreach ($hits as $hit) {
            $className = 'Coyote\\Services\\Elasticsearch\Normalizers\\' . ucfirst(camel_case(str_singular($hit['_type'])));

            if (class_exists($className)) {
                $this->hits[] = new $className($hit);
            }
        }
    }

    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->hits);
    }
}
