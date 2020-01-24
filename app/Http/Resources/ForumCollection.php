<?php

namespace Coyote\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Resources\MergeValue;
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

        return $this->nested($categories);
    }

    /**
     * @param Collection|\Coyote\Forum[] $categories
     * @param int $parentId
     * @return array
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
            ->keyBy('parent_id');

        // we merge children with parent element
        foreach ($children as $parentId => $child) {
            if (!empty($parents[$parentId])) {
                $parents[$parentId] = $this->mergeChildren($parents[$parentId], $child);
            }
        }

        return $parents->toArray();
    }

    /**
     * @param array $parent
     * @param array $child
     * @return array
     */
    private function mergeChildren(array $parent, array $child): array
    {
        if (!isset($parent['children'])) {
            $parent['children'] = [];
        }

        array_push($parent['children'], $child);

        $parent['topics'] += $child['topics'];
        $parent['posts'] += $child['posts'];

        // there's no posts in category
        if (!isset($child[0]->data)) {
            return $parent;
        }

        $post = &$child[0]->data['post'];
        $topic = &$child[0]->data['topic'];

        // there's no posts in main category
        if (!isset($parent[0]->data)) {
            $parent[0] = new MergeValue(['post' => ['created_at' => '']]);
        }

        // created_at contains last post's created date.
        if ($post['created_at'] > $parent[0]->data['post']['created_at']) {
            $parent[0]->data['post'] = $post;
            $parent[0]->data['topic'] = $topic;
            $parent['is_read'] = $child['is_read'];
        }

        return $parent;
    }
}
