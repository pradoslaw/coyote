<?php

namespace Coyote\Services\Forum\TreeBuilder;

use Coyote\Forum;

class JsonDecorator
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
            $result[] = $this->getItem($parent, false);

            if (isset($parent->children)) {
                foreach ($parent->children as $child) {
                    $result[] = $this->getItem($child, true);
                }
            }
        }

        return $result;
    }

    private function getItem(Forum $forum, bool $indent): array
    {
        return ['id' => $forum->id, 'name' => $forum->name, 'indent' => $indent];
    }
}
