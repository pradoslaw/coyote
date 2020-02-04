<?php

namespace Coyote\Http\Resources;

use Coyote\Services\Guest;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Resources\MergeValue;
use Illuminate\Support\Collection;

class ForumCollection extends ResourceCollection
{
    /**
     * @var int|null
     */
    protected $parentId;

    /**
     * @var Guest|null
     */
    protected $guest;

    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = ForumResource::class;

    /**
     * @param $collection
     * @return ForumCollection
     */
    public static function factory($collection)
    {
        return (new self($collection))->setGuest(app(Guest::class));
    }

    /**
     * @param int $parentId
     * @return $this
     */
    public function setParentId(int $parentId)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * @param Guest|null $guest
     * @return $this
     */
    public function setGuest(?Guest $guest)
    {
        $this->guest = $guest;

        return $this;
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $categories = $this
            ->collection
            ->map(function (ForumResource $resource) use ($request) {
                return $resource->setGuest($this->guest)->toArray($request);
            })
            ->toArray();

        return $this->nested($categories, $this->parentId);
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
        $childrenGroup = $categories
            ->filter(function ($item) use ($parentId) {
                return $item['parent_id'] != $parentId;
            })
            ->sortBy('order')
            ->groupBy('parent_id');

        // we merge children with parent element
        foreach ($childrenGroup as $parentId => $children) {
            foreach ($children as $child) {
                if (!empty($parents[$parentId])) {
                    $parents[$parentId] = $this->mergeChildren($parents[$parentId], $child);
                }
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
            $parent[0] = new MergeValue(['post' => $post, 'topic' => $topic]);
            $parent['is_read'] = $child['is_read'];

            return $parent;
        }

        // created_at contains last post's created date.
        if ($post['created_at'] > $parent[0]->data['post']['created_at']) {
            $parent[0]->data['post'] = $post;
            $parent[0]->data['topic'] = $topic;
        }

        // there are new topics in child category. parent category also has to be mark as unread
        if (!$child['is_read']) {
            $parent['is_read'] = false;
        }

        return $parent;
    }
}
