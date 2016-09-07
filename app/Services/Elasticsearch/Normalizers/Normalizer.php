<?php

namespace Coyote\Services\Elasticsearch\Normalizers;

abstract class Normalizer
{
    /**
     * @var array
     */
    protected $hit;

    /**
     * @var array
     */
    protected $source;

    /**
     * @param array $hit
     */
    public function __construct(array $hit)
    {
        $this->hit = $hit;
        $this->source = &$this->hit['_source'];
    }

    /**
     * @return string
     */
    public function createdAt()
    {
        return $this->hit['_source']['created_at'];
    }

    /**
     * @return string
     */
    public function updatedAt()
    {
        return $this->hit['_source']['updated_at'];
    }

    /**
     * @param string $key
     * @return string
     */
    protected function getHighlight($key)
    {
        return isset($this->hit['highlight'][$key])
            ? $this->hit['highlight'][$key][0]
            : str_limit($this->hit['_source'][$key], 160);
    }

    /**
     * Only for twig templates. Ability to call methods in snake case style.
     *
     * @param string $name
     * @param array $arguments
     * @return string
     */
    public function __call($name, $arguments = [])
    {
        return $this->{camel_case($name)}();
    }
}
