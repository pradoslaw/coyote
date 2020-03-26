<?php

namespace Coyote\Services\Forum\TreeBuilder;

use Illuminate\Support\Collection;

class Builder
{
    /**
     * @var \Coyote\Forum[]|Collection
     */
    private $forums;

    /**
     * @var int|null
     */
    private $parentId;

    /**
     * @param Collection|\Coyote\Forum[] $forums
     * @param int $parentId
     */
    public function __construct($forums, $parentId = null)
    {
        $this->forums = $forums;
        $this->parentId = $parentId;
    }

    /**
     * @param Collection|\Coyote\Forum[] $forums
     */
    public function setForums($forums)
    {
        $this->forums = $forums;
    }

    /**
     * @return Collection
     */
    public function build()
    {
        // extract only main categories
        $parents = $this->forums->where('parent_id', $this->parentId)->keyBy('id');

        // extract only children categories
        $children = $this
            ->forums
            ->filter(function ($item) {
                return $item->parent_id != $this->parentId;
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
