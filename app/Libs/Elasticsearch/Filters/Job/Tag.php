<?php

namespace Coyote\Elasticsearch\Filters\Job;

use Coyote\Elasticsearch\DslInterface;
use Coyote\Elasticsearch\Filters\Terms;
use Coyote\Elasticsearch\QueryBuilderInterface;

class Tag extends Terms implements DslInterface
{
    /**
     * @var array
     */
    protected $tags = [];

    /**
     * Tags constructor.
     * @param array $tags
     */
    public function __construct($tags = [])
    {
        $this->setTags($tags);
    }

    /**
     * @param $tag
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
        parent::__construct('tag_original', $this->tags);
        return parent::apply($queryBuilder);
    }
}