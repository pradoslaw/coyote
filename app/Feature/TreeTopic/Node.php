<?php
namespace Coyote\Feature\TreeTopic;

class Node
{
    public array $children = [];

    public function __construct(public int $id, public mixed $payload) {}

    public function add(Node $child): void
    {
        $this->children[] = $child;
    }

    public function hasChildren(): bool
    {
        return !empty($this->children);
    }
}
