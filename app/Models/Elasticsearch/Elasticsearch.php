<?php

namespace Coyote\Elasticsearch;

trait Elasticsearch
{
    /**
     * Index data in elasticsearch
     *
     * @return mixed
     */
    public function putToIndex()
    {
        $params = $this->getParams();
        $params['id'] = $this->getKey();

        $params['body'] = $this->modelToArray($this->getBody());

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
     * Basic elasticsearch params
     *
     * @return array
     */
    protected function getParams()
    {
        return [
            'index' => $this->getIndexName(),
            'type' => $this->getTable(),
            'id' => $this->getKey()
        ];
    }

    /**
     * Convert model to array
     *
     * @param $collection
     * @return mixed
     */
    protected function modelToArray($collection)
    {
        if (is_object($collection) && method_exists($collection, 'toArray')) {
            $collection = $collection->toArray();
        }

        foreach ($collection as &$value) {
            if (is_object($value) && method_exists($value, 'toArray')) {
                $value = $this->modelToArray($value);
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
        return app('Elasticsearch');
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
     * Default data to index in elasticsearch
     *
     * @return mixed
     */
    protected function getBody()
    {
        return $this->toArray();
    }
}