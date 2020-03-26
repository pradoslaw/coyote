<?php

namespace Coyote\Services\Forum;

use Illuminate\Support\Collection;

/**
 * @deprecated
 */
class TreeBuilder
{
    /**
     * @param \Coyote\Forum[] $categories
     * @return array
     */
    public function listById($categories)
    {
        return $this->makeList($categories, 'id');
    }

    /**
     * @param \Coyote\Forum[] $categories
     * @return array
     */
    public function listBySlug($categories)
    {
        return $this->makeList($categories, 'slug');
    }

    /**
     * @param \Coyote\Forum[] $categories
     * @return array
     */
    public function flat($categories)
    {
        $list = [];

        foreach ($this->buildNested($categories) as $parent) {
            $list[] = $parent;

            if (isset($parent->children)) {
                foreach ($parent->children as $child) {
                    $list[] = $child;
                }
            }
        }

        return $list;
    }

    /**
     * @param \Coyote\Forum[] $categories
     * @param int|null $root
     * @return array
     */
    private function makeList($categories, $root)
    {
        $list = [];

        foreach ($this->buildNested($categories) as $parent) {
            $list[$parent->{$root}] = $parent->name;

            if (isset($parent->children)) {
                foreach ($parent->children as $child) {
                    $list[$child->{$root}] = str_repeat('&nbsp;', 4) . $child->name;
                }
            }
        }

        return $list;
    }

    /**
     * @param Collection|\Coyote\Forum[] $rowset
     * @param int $parentId
     * @return Collection
     */
    private function buildNested($rowset, $parentId = null)
    {
        // extract only main categories
        $parents = $rowset->where('parent_id', $parentId)->keyBy('id');

        // extract only children categories
        $children = $rowset
            ->filter(function ($item) use ($parentId) {
                return $item->parent_id != $parentId;
            })
            ->groupBy('parent_id');

        // we merge children with parent element
        foreach ($children as $parentId => $child) {
            if (!empty($parents[$parentId])) {
                $parents[$parentId]->children = $child;
            }
        }

        return $parents;
    }
}
