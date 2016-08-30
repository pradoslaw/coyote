<?php

namespace Coyote;

use Coyote\Services\Elasticsearch\Response;
use Coyote\Services\Elasticsearch\ResponseInterface;
use Illuminate\Contracts\Support\Arrayable;

trait Searchable
{
    protected $response = Response::class;

    /**
     * Index data in elasticsearch
     *
     * @return mixed
     */
    public function putToIndex()
    {
        $params = $this->getParams();
        $params['body'] = $this->buildArray($this->getIndexBody());

        return $this->getClient()->index($params);
    }

    /**
     * Delete document from index
     *
     * @return mixed
     */
    public function deleteFromIndex()
    {
        return $this->getClient()->delete($this->getParams());
    }

    /**
     * @param array $body
     * @return ResponseInterface
     */
    public function search($body)
    {
        $params = $this->getParams();
        $params['body'] = $body;

        return $this->buildResponse($this->getClient()->search($params));
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
     * @param $response
     * @return ResponseInterface
     */
    protected function buildResponse($response)
    {
        return app($this->getResponse(), [$response]);
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
     * @param $collection
     * @return mixed
     */
    protected function buildArray($collection)
    {
        if (is_object($collection) && $collection instanceof Arrayable) {
            $collection = $collection->toArray();
        }

        foreach ($collection as &$value) {
            if (is_object($value) && $collection instanceof Arrayable) {
                $value = $this->buildArray($value);
            }
        }

        return $collection;
    }

    /**
     * Get client instance
     *
     * @return \Illuminate\Foundation\Application|mixed
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

    /**
     * @param string $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Default data to index in elasticsearch
     *
     * @return mixed
     */
    protected function getIndexBody()
    {
        $body = $this->toArray();

        foreach (['created_at', 'updated_at', 'deadline_at', 'last_post_created_at'] as $column) {
            if (!empty($body[$column])) {
                $body[$column] = date('Y-m-d H:i:s', strtotime($body[$column]));
            }
        }

        return $body;
    }
}
