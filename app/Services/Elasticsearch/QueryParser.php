<?php

namespace Coyote\Services\Elasticsearch;

/**
 * Parse elasticsearch query like: lorem ipsum user:admin ip:127.0.0.1 and returns query and filters
 *
 * @package Coyote\Services\Elasticsearch
 */
class QueryParser
{
    /**
     * @var array
     */
    protected $allowedKeywords = [];

    /**
     * @var string
     */
    protected $inputQuery;

    /**
     * @var array
     */
    protected $filters = [];

    /**
     * @var
     */
    protected $filteredQuery;

    /**
     * QueryParser constructor.
     * @param $inputQuery
     * @param string[] $allowedKeywords   Keywords like "ip", "user", "browser"
     */
    public function __construct($inputQuery, array $allowedKeywords)
    {
        $this->inputQuery = $inputQuery;
        $this->allowedKeywords = $allowedKeywords;

        $this->parse();
    }

    /**
     * @return array    like ['ip' => '127.0.0.1', 'user' => 'admin']
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param string $filter
     * @return mixed|null
     */
    public function getFilter($filter)
    {
        return isset($this->filters[$filter]) ? $this->filters[$filter] : null;
    }

    /**
     * @param string $filter
     * @return string
     */
    public function pullFilter($filter)
    {
        $data = $this->getFilter($filter);
        unset($this->filters[$filter]);

        return $data;
    }

    /**
     * @param string $filter
     */
    public function removeFilter($filter)
    {
        unset($this->filters[$filter]);
    }

    /**
     * @param string|array $query
     */
    public function appendQuery($query)
    {
        if (is_array($query)) {
            $key = key($query);

            if (!empty($query[$key])) {
                $this->filteredQuery .= ' ' . $key . ':"' . $query[$key] . '"';
            }
        } elseif (is_string($query)) {
            $this->filteredQuery .= ' ' . $query;
        }
    }

    /**
     * @return string
     */
    public function getFilteredQuery()
    {
        return $this->filteredQuery;
    }

    /**
     * Transform input "lorem ipsum user:admin ip:127.0.0.1" to "lorem ipsum"     *
     */
    protected function parse()
    {
        preg_match('/(user|ip)\:\"?([0-9a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ._ -]{1,})\"?/', $this->inputQuery, $matches);

        if (empty($matches)) {
            return;
        }

        list($original, $key, $value) = $matches;

        $this->filteredQuery = str_replace($original, '', $this->inputQuery);
        $this->filters[$key] = trim($value, '"');
    }
}
