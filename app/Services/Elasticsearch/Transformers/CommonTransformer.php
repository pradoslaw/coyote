<?php

namespace Coyote\Services\Elasticsearch\Transformers;

class CommonTransformer implements TransformerInterface
{
    /**
     * @var array|\Illuminate\Support\Collection
     */
    protected $hits = [];

    /**
     * @var array
     */
    protected $aggregations = [];

    /**
     * @var int
     */
    protected $total = 0;

    /**
     * Response constructor.
     * @param array $response
     */
    public function __construct($response)
    {
        if (isset($response['hits'])) {
            $this->hits = $this->collect($response['hits']['hits']);
            $this->total = $response['hits']['total'];

            if (isset($response['aggregations'])) {
                $this->aggregations = ($response['aggregations']);
            }
        }
    }

    /**
     * Transform results array to laravel's collection
     *
     * @param array $array
     * @return \Illuminate\Support\Collection
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
    public function total()
    {
        return $this->total;
    }

    /**
     * Get Hits
     *
     * Get the raw hits from
     * Elasticsearch results.
     *
     * @return array|\Illuminate\Support\Collection
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
        if (!$this->total) {
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
        return $this->hits->$name(...$arguments);
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
     * @param null $name
     * @return \Illuminate\Support\Collection|array
     */
    public function getAggregations($name = null)
    {
        if (!$name) {
            return $this->aggregations;
        }

        $data = array_get($this->aggregations, "$name.buckets");
        return collect($data)->pluck('doc_count', 'key');
    }

    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->hits->pluck('_source')->toArray());
    }
}
