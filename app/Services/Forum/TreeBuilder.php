<?php

namespace Coyote\Services\Forum;

use Illuminate\Support\Collection;

/**
 * @deprecated
 */
class TreeBuilder
{
    /**
     * Make a tree-like structure:
     *
     * Section
     * -- Category
     *    -- Subcategory
     *
     * @param Collection $categories
     * @param null|int $parentId
     * @return Collection
     */
    public function sections($categories, $parentId = null)
    {
        // execute query and fetch all forum categories
        $parents = $this->buildNested($categories, $parentId);
        $parents = $this->fillUpSectionNames($parents);

        foreach ($parents as &$parent) {
            if (isset($parent->children)) {
                foreach ($parent->children as $child) {
                    $parent->topics += $child->topics;
                    $parent->posts += $child->posts;

                    // created_at contains last post's created date.
                    if ($child->created_at > $parent->created_at) {
//                        if ($child->forum_unread) {
//                            $parent->forum_unread = true;
//                        }

                        $parent->last_post_id = $child->last_post_id;
//                        $parent->topic_unread = $child->topic_unread;
                        $parent->created_at = $child->created_at;
                        $parent->user_id = $child->user_id;
//                        $parent->photo = $child->photo;
//                        $parent->is_active = $child->is_active;
//                        $parent->is_confirm = $child->is_confirm;
//                        $parent->user_name = $child->user_name;
//                        $parent->anonymous_name = $child->anonymous_name;
//                        $parent->subject = $child->subject;
//                        $parent->topic_slug = $child->topic_slug;
//                        $parent->route = $child->route;
                    }
                }
            }
        }

        // finally... group by sections
        return $parents->groupBy('section');
    }

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
     * @param Collection $parents
     * @return Collection
     */
    private function fillUpSectionNames($parents)
    {
        // we must fill section field in every row just to group rows by section name.
        $section = '';
        foreach ($parents as &$parent) {
            if ($parent->section) {
                $section = $parent->section;
            } else {
                $parent->section = $section;
            }
        }

        return $parents;
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
