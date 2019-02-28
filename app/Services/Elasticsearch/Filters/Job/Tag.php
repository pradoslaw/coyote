<?php

namespace Coyote\Services\Elasticsearch\Filters\Job;

use Coyote\Services\Elasticsearch\DslInterface;
use Coyote\Services\Elasticsearch\Filters\Terms;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;

class Tag extends Terms implements DslInterface
{
    /**
     * @var string[]
     */
    protected $tags = [];

    /**
     * Tags constructor.
     * @param string[]|string $tags
     */
    public function __construct($tags = [])
    {
        $this->setTags($tags);
    }

    /**
     * @param string[]|string $tag
     * @return $this
     */
    public function addTag($tag)
    {
        if (is_array($tag)) {
            foreach ($tag as $value) {
                $this->addTag($value);
            }
        } else {
            $this->tags[] = $tag;
        }

        return $this;
    }

    /**
     * @param $tags
     * @return $this
     */
    public function setTags($tags)
    {
        if (!is_array($tags)) {
            $tags = [$tags];
        }

        $this->tags = $tags;
        return $this;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return mixed
     */
    public function apply(QueryBuilderInterface $queryBuilder)
    {
        parent::__construct('tags.original', $this->tags);

        return parent::apply($queryBuilder);
    }
}
