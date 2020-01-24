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

        $parents = $this->nested($categories);

        foreach ($parents as &$parent) {

            if (isset($parent['children'])) {
                foreach ($parent['children'] as $child) {
                    $parent['topics'] += $child['topics'];
                    $parent['posts'] += $child['posts'];

                    if (!isset($child[0])) {
                        continue;
                    }

                    $post = &$child[0]->data['post'];
                    $topic = &$child[0]->data['topic'];

                    // created_at contains last post's created date.
                    if ($post['created_at'] > $parent[0]->data['post']['created_at']) {
                        $parent[0]->data['post'] = $post;
                        $parent[0]->data['topic'] = $topic;
                        $parent['is_read'] = $child['is_read'];

                    }
                }
            }
        }

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
