<?php

namespace Coyote\Elasticsearch;

use ArrayIterator;

class Response implements \Countable, \IteratorAggregate
{
    /**
     * @var array|\Illuminate\Support\Collection
     */
    protected $hits = [];

    /**
     * @var int
     */
    protected $totalHits = 0;

    /**
     * Response constructor.
     * @param $response
     */
    public function __construct($response)
    {
        if (isset($response['hits'])) {
            $this->hits = collect($response['hits']['hits']);
            $this->totalHits = $response['hits']['total'];
        }
    }

    /**
     * Total Hits
     *
     * @return int
     */
    public function totalHits()
    {
        return $this->totalHits;
    }

    /**
     * Get Hits
     *
     * Get the raw hits array from
     * Elasticsearch results.
     *
     * @return array
     */
    public function getHits()
    {
        return $this->hits;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->getHits()->$name(...$arguments);
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->hits->count();
    }

    /**
     * @return static
     */
    public function getHighlights()
    {
        return $this->hits->pluck('highlight', '_source.id');
    }

    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->hits->pluck('_source')->toArray());
    }
}