<?php

namespace Coyote\Services\Elasticsearch;

class MoreLikeThis implements DslInterface
{
    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @var array
     */
    protected $docs = [];

    /**
     * @param array $fields
     */
    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    /**
     * @param array $doc
     */
    public function addDoc(array $doc)
    {
        $this->docs[] = $doc;
    }

    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return array
     */
    public function apply(QueryBuilderInterface $queryBuilder)
    {
        $result = [
            'more_like_this' => [
                'fields' => $this->fields,
                'like' => [],
                'min_term_freq' => 1,
                'min_doc_freq' => 1,
                'max_query_terms' => 5
            ]
        ];

        foreach ($this->docs as $doc) {
            $result['more_like_this']['like'][] = $doc;
        }

        return $result;
    }
}
