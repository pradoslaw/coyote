<?php

namespace Coyote\Services\Forum\TreeBuilder;

class ListDecorator
{
    private const INDENT_CHAR = '&nbsp;';

    /**
     * @var Builder
     */
    private $builder;

    /**
     * @var string
     */
    private $key = 'slug';

    /**
     * @param Builder $builder
     */
    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function setKey(string $key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return array
     */
    public function build(): array
    {
        $result = [];

        foreach ($this->builder->build() as $parent) {
            $result[$parent->{$this->key}] = $parent->name;

            if (isset($parent->children)) {
                foreach ($parent->children as $child) {
                    $result[$child->{$this->key}] = str_repeat(self::INDENT_CHAR, 4) . $child->name;
                }
            }
        }

        return $result;
    }
}
