<?php

namespace Coyote;

use Coyote\Services\Elasticsearch\QueryBuilderInterface;
use Coyote\Services\Elasticsearch\ResultSet;
use Elasticsearch\Common\Exceptions\Missing404Exception;

trait Searchable
{
    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    abstract public function getTable();

    /**
     * Get the value of the model's primary key.
     *
     * @return mixed
     */
    abstract public function getKey();


    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return ResultSet
     */
    public function search(QueryBuilderInterface $queryBuilder)
    {
        return new ResultSet($this->performSearch($queryBuilder->build()));
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
     * Basic elasticsearch params
     *
     * @return array
     */
    protected function getParams()
    {
        $params = [
            'index'     => $this->getIndexName(),
            'type'      => '_doc'
        ];

        if ($this->getKey()) {
            $params['id'] = str_singular($this->getTable()) . '_' . $this->getKey();
        }

        return $params;
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
     * Get default index name from config
     *
     * @return mixed
     */
    protected function getIndexName()
    {
        return config('elasticsearch.default_index');
    }
}
