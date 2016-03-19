<?php

namespace Coyote\Elasticsearch\Response;

use ArrayIterator;

class Standard implements \Countable, \IteratorAggregate, ResponseInterface
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
     * @param array $response
     */
    public function __construct($response)
    {
        if (isset($response['hits'])) {
            $this->hits = $this->collect($response['hits']['hits']);
            $this->totalHits = $response['hits']['total'];
        }
    }

    /**
     * Transform results array to laravel's collection
     *
     * @param array $array
     * @return array|\Illuminate\Support\Collection
     */
    protected function collect(array $array)
    {
        $array = collect($array);

        foreach ($array as $key => $item) {
            if (is_array($item)) {
                $array[$key] = $this->collect($item);
            }
        }

        return $array;
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
     * Get the raw hits from
     * Elasticsearch results.
     *
     * @return array
     */
    public function getHits()
    {
        return $this->hits;
    }

    /**
     * Get _source element from raw hits
     *
     * @return array|\Illuminate\Support\Collection
     */
    public function getSource()
    {
        if (!$this->totalHits) {
            return [];
        }

        return $this->hits->pluck('_source');
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