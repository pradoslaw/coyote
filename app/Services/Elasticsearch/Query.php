<?php

namespace Coyote\Services\Elasticsearch;

class Query implements DslInterface
{
    /**
     * @var array
     */
    private $hash = [];

    /**
     * @var string
     */
    protected $query;

    /**
     * @var array
     */
    protected $fields;

    /**
     * Query constructor.
     * @param string $query
     * @param array $fields
     */
    public function __construct($query, $fields)
    {
        $this->query = $query;
        $this->fields = $fields;
    }

    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return array
     */
    public function apply(QueryBuilderInterface $queryBuilder)
    {
        $body = $queryBuilder->getBody();

        $body['query']['filtered']['query'] = [
            'query_string' => [
                'query' => $this->escape($this->query),
                'fields' => $this->fields
            ]
        ];

        return $body;
    }

    /**
     * @param $query
     * @return mixed
     */
    protected function escape($query)
    {
        $escape = function ($str) {
            return str_replace('\:', ':', preg_quote($str, '+-!{}[]^"~*?\\'));
        };

        if (strpos($query, '"') !== false) {
            $raw = $this->hashInline($query);
            $query = $escape($raw);

            return $this->unhash($query);
        }
        return $escape($query);
    }

    /**
     * @param $line
     * @return string
     */
    protected function hashInline($line)
    {
        $raw = '';

        while ($haystack = strpbrk($line, '"')) {
            // pozycja znaku "
            $markerPosition = strpos($line, '"');

            // zwracamy hash
            $hash = $this->hashPart($haystack);
            if (!$hash) {
                $raw .= substr($line, 0, $markerPosition + 1);
                $line = substr($line, $markerPosition + 1);
            }

            // doklejamy do rezultatu tekst poprzedzajacy wystapienie znaku "
            $raw .= substr($line, 0, $markerPosition);
            // doklejamy hash zamiast oryginalnego tekstu
            $raw .= $hash['hash'];

            // usuwamy z oryginalnej zmiennej porcje przetworzonego tekstu
            $line = substr($line, $markerPosition + $hash['extent']);
        }

        $raw .= $line;
        return $raw;
    }

    /**
     * @param $text
     * @return array|null
     */
    protected function hashPart($text)
    {
        if (preg_match('/^("+)[ ]*(.+?)[ ]*(?<!")\1(?!")/s', $text, $matches)) {
            $uniqId = uniqid();
            $this->hash[$uniqId] = $matches[0];

            return ['hash' => $uniqId, 'extent' => mb_strlen($matches[0])];
        } else {
            return null;
        }
    }

    /**
     * @param $text
     * @return mixed
     */
    protected function unhash($text)
    {
        if (!empty($this->hash)) {
            foreach ($this->hash as $uniqId => $data) {
                $text = str_replace($uniqId, $data, $text);
            }
        }

        return $text;
    }
}