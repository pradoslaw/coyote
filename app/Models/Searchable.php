<?php

namespace Coyote;

use Coyote\Services\Elasticsearch\CharFilters\CharFilterInteface;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;
use Coyote\Services\Elasticsearch\ResultSet;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Illuminate\Contracts\Support\Arrayable;

trait Searchable
{
    /**
     * @var string
     */
    protected $charFilter;

    /**
     * Index data in elasticsearch
     *
     * @return mixed
     * @throws \Exception
     */
    public function putToIndex()
    {
        $params = $this->getParams();
        $params['body'] = $this->filterData($this->getIndexBody());

        if (empty($params['body'])) {
            return null;
        }

        return $this->getClient()->index($params);
    }

    /**
     * Delete document from index
     *
     * @throws Missing404Exception,
     * @throws \Exception
     * @return mixed
     */
    public function deleteFromIndex()
    {
        $result = false;

        try {
            $result = $this->getClient()->delete($this->getParams());
        } catch (Missing404Exception $e) {
            // ignore 404 errors...
        } catch (\Exception $e) {
            throw $e;
        }

        return $result;
    }

    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return ResultSet
     */
    public function search(QueryBuilderInterface $queryBuilder)
    {
        return new ResultSet($this->performSearch($queryBuilder->build()));
    }

    /**
     * Put mapping to elasticsearch's type
     */
    public function putMapping()
    {
        $mapping = $this->getMapping();

        if (!empty($mapping)) {
            $params = $this->getParams();
            $params['body'] = $mapping;

            $this->getClient()->indices()->putMapping($params);
        }
    }

    /**
     * @param string $filter
     */
    public function setCharFilter(string $filter)
    {
        $this->charFilter = $filter;
    }

    /**
     * @param array $body
     * @return array
     */
    protected function performSearch($body)
    {
        // show build query in laravel's debugbar
        debugbar()->debug(json_encode($body));
        debugbar()->debug($body);

        $params = $this->getParams();
        $params['body'] = $body;

        debugbar()->startMeasure('Elasticsearch');

        $result = $this->getClient()->search($params);

        debugbar()->stopMeasure('Elasticsearch');

        return $result;
    }

    /**
     * Default data to index in elasticsearch
     *
     * @return mixed
     */
    protected function getIndexBody()
    {
        $body = $this->toArray();

        foreach ($this->dates as $column) {
            if (!empty($body[$column])) {
                $body[$column] = date('Y-m-d H:i:s', strtotime($body[$column]));
            }
        }

        return $body;
    }

    /**
     * Get model's mapping
     *
     * @return array
     */
    protected function getMapping()
    {
        return [
            $this->getTable() => [
                'properties' => $this->mapping
            ]
        ];
    }

    /**
     * Basic elasticsearch params
     *
     * @return array
     */
    protected function getParams()
    {
        $params = [
            'index'     => $this->getIndexName(),
            'type'      => $this->getTable()
        ];

        if ($this->getKey()) {
            $params['id'] = $this->getKey();
        }

        return $params;
    }

    /**
     * Convert model to array
     *
     * @param mixed $data
     * @return array
     */
    protected function filterData($data)
    {
        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }

        foreach ($data as &$value) {
            if (is_object($value) && $data instanceof Arrayable) {
                $value = $this->filterData($value);
            }
        }

        if ($this->charFilter) {
            $data = $this->getCharFilter()->filter($data);
        }

        return $data;
    }

    /**
     * Get client instance
     *
     * @return \Elasticsearch\Client
     */
    protected function getClient()
    {
        return app('elasticsearch');
    }

    /**
     * @return CharFilterInteface
     */
    protected function getCharFilter(): CharFilterInteface
    {
        return app($this->charFilter);
    }

    /**
     * Get default index name from config
     *
     * @return mixed
     */
    protected function getIndexName()
    {
        return config('elasticsearch.default_index');
    }
}
