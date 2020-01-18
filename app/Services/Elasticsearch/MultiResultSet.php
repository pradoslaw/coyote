<?php

namespace Coyote\Services\Elasticsearch;

class MultiResultSet extends ResultSet
{
    /**
     * @param array $hits
     * @return array
     */
    protected function map(array $hits)
    {
        $result = [];

        foreach ($hits as $hit) {
            $className = __NAMESPACE__ . '\\Normalizers\\' . ucfirst(camel_case($hit['_source']['model']));

            if (class_exists($className)) {
                $result[] = new $className($hit);
            }
        }

        return $result;
    }

    /**
     * @param mixed $models
     * @return \Illuminate\Support\Collection
     */
    protected function collect($models)
    {
        return $models;
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
