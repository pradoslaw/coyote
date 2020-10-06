<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\ForumRepositoryInterface;
use Coyote\Tag;
use Coyote\Forum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ForumRepository extends Repository implements ForumRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return Forum::class;
    }

    /**
     * @inheritdoc
     */
    public function categories($guestId, $parentId = null)
    {
        $this->applyCriteria();

        $result = $this
            ->model
            ->addSelect('forums.*')
            ->withForumMarkTime($guestId)
            ->with(['post' => function (HasOne $builder) use ($guestId) {
                return $builder
                    ->select(['id', 'user_id', 'topic_id', 'forum_id', 'user_name', 'created_at'])
                    ->with([
                        'topic' => function (BelongsTo $builder) use ($guestId) {
                            return $builder->select(['topics.id', 'subject', 'slug', 'topics.forum_id', 'last_post_created_at'])->withTopicMarkTime($guestId);
                        },
                        'user' => function (BelongsTo $builder) {
                            return $builder->select(['id', 'name', 'deleted_at', 'is_blocked', 'photo'])->withTrashed();
                        }
                    ]);
            }])
            ->when($parentId, function (Builder $builder) use ($parentId) {
                return $builder->where('parent_id', $parentId);
            })
            ->get();

        $this->resetModel();

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function setup($userId, array $data)
    {
        $this->deleteSetup($userId);

        Forum\Order::insert(
            array_map(
                function ($item) use ($userId) {
                    return $item + ['user_id' => $userId];
                },
                $data
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function deleteSetup($userId)
    {
        Forum\Order::where('user_id', $userId)->delete();
    }

    /**
     * @inheritdoc
     */
    public function findHiddenIds($userId)
    {
        if ($userId === null) {
            return [];
        }

        $result = $this
            ->model
            ->select(['forum_id', 'forums.id AS child_forum_id'])
            ->where('user_id', $userId)
            ->where('is_hidden', 1)
            ->rightJoin('forum_orders', 'parent_id', '=', 'forum_orders.forum_id')
            ->get();

        return array_filter(array_unique(
            array_merge($result->pluck('forum_id')->toArray(), $result->pluck('child_forum_id')->toArray())
        ));
    }

    /**
     * @inheritdoc
     */
    public function list()
    {
        return $this->applyCriteria(function () {
            return $this->model->addSelect('forums.id', 'name', 'slug', 'forums.section', 'parent_id')->orderBy('forums.order')->get();
        });
    }

    /**
     * @return array
     */
    public function getTagsCloud()
    {
        return $this
            ->app
            ->make(Tag::class)
            ->select(['tags.id', 'name', 'logo', $this->raw('COUNT(*) AS count')])
            ->join('topic_tags', 'tags.id', '=', 'tag_id')
            ->join('topics', 'topics.id', '=', 'topic_id')
            ->whereNull('topics.deleted_at')
            ->whereNull('tags.deleted_at')
            ->groupBy('name', 'logo', 'tags.id')
            ->orderBy($this->raw('COUNT(*)'), 'DESC')
            ->limit(10)
            ->get()
            ->pluck('count', 'name')
            ->toArray();
    }

    /**
     * @param array $tags
     * @return array
     */
    public function getTagsWeight(array $tags)
    {
        $list = [];

        foreach ($tags as $tag) {
            $list[] = "('$tag', 0)";
        }

        return $this
            ->app
            ->make(Tag::class)
            ->select(['tags.id', 'custom.name', 'logo', $this->raw('GREATEST(custom.count, count(topic_tags.id)) AS count')])
            ->fromRaw("(VALUES" . implode(',', $list) . ") AS custom(name, count)")
            ->leftJoin('tags', 'tags.name', '=', 'custom.name')
            ->leftJoin('topic_tags', 'tag_id', '=', 'tags.id')
            ->leftJoin('topics', 'topics.id', '=', 'topic_id')
            ->whereNull('topics.deleted_at')
            ->groupBy('custom.name', 'logo', 'tags.id', 'custom.count')
            ->orderBy($this->raw('COUNT(*)'), 'DESC')
            ->get();
    }

    /**
     * @param int $id
     */
    public function up($id)
    {
        $this->changeForumOrder($id, '<');
    }

    /**
     * @param int $id
     */
    public function down($id)
    {
        $this->changeForumOrder($id, '>');
    }

    /**
     * @param int $id
     * @param string $operator
     */
    private function changeForumOrder($id, $operator)
    {
        /** @var \Coyote\Forum $forum */
        /** @var \Coyote\Forum $other */
        $forum = $this->model->findOrFail($id, ['id', 'order', 'parent_id']);

        $other = $this
            ->model
            ->select(['id', 'order', 'parent_id'])
            ->where('parent_id', $forum->parent_id)
            ->where('order', $operator, $forum->order)
            ->orderBy('order', $operator == '<' ? 'DESC' : 'ASC')
            ->first();

        if ($other) {
            $forum->order = $other->order;
            $other->order = $forum->getOriginal('order');

            $forum->save();
            $other->save();
        }
    }
}
