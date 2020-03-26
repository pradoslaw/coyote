<?php

namespace Coyote\Services\Forum\TreeBuilder;

class FlatDecorator
{
    /**
     * @var Builder
     */
    private $builder;

    /**
     * @param Builder $builder
     */
    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * @return array
     */
    public function build(): array
    {
        $result = [];

        foreach ($this->builder->build() as $parent) {
            $result[] = $parent;

            if (isset($parent->children)) {
                foreach ($parent->children as $child) {
                    $result[] = $child;
                }
            }
        }

        return $result;
    }
}
