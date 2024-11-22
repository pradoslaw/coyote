<?php
namespace Coyote\Feature\TreeTopic;

class ArrayLinkedSorter
{
    public function sort(array $array, string $idField, string $parentIdField): array
    {
        /** @var Node[] $nodes */
        $nodes = [];

        foreach ($array as $post) {
            $postId = $post[$idField] ?? -1;
            $nodes[$postId] = new Node($postId, $post);
            if ($post[$parentIdField] !== null) {
                $parentId = $post[$parentIdField];
                $nodes[$parentId]->add($nodes[$post[$idField]]);
            }
        }
        $values = [];
        foreach ($nodes as $node) {
            $this->traverseTree($node, $values);
        }
        return \array_values($values);
    }

    private function traverseTree(Node $node, array &$list): void
    {
        if (!\array_key_exists($node->id, $list)) {
            $list[$node->id] = $node->payload;
        }
        if ($node->hasChildren()) {
            foreach ($node->children as $child) {
                $this->traverseTree($child, $list);
            }
        }
    }
}
