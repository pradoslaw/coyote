<?php

namespace Coyote\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;

class ForumCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = ForumResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $categories = parent::toArray($request);
        return $categories;

//        $parents = $this->nested($categories);

//        foreach ($parents as &$parent) {
//            if (isset($parent->children)) {
//                foreach ($parent->children as $child) {
//                    $parent->topics += $child->topics;
//                    $parent->posts += $child->posts;
//
//                    // created_at contains last post's created date.
//                    if ($child->created_at > $parent->created_at) {
//                        $parent->last_post_id = $child->last_post_id;
//                        $parent->post = $child->post;
//                    }
//                }
//            }
//        }

        return $parents->toArray();
    }

    /**
     * @param Collection|\Coyote\Forum[] $categories
     * @param int $parentId
     * @return Collection
     */
    private function nested($categories, $parentId = null)
    {
        $categories = collect($categories);

        // extract only main categories
        $parents = $categories->where('parent_id', $parentId)->keyBy('id');

        // extract only children categories
        $children = $categories
            ->filter(function ($item) use ($parentId) {
                return $item['parent_id'] != $parentId;
            })
            ->groupBy('parent_id');

        // we merge children with parent element
        foreach ($children as $parentId => $child) {
            if (!empty($parents[$parentId])) {
                $parents[$parentId] = array_merge($parents[$parentId], ['children' => $child->toArray()]);
            }
        }

        return $parents;
    }
}
