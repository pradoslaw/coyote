<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\Forum\OrderRepositoryInterface;
use Coyote\Repositories\Contracts\ForumRepositoryInterface;
use Coyote\Topic;
use Coyote\Forum;
use Illuminate\Container\Container as App;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\JoinClause;

class ForumRepository extends Repository implements ForumRepositoryInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    public $order;

    /**
     * @inheritdoc
     */
    public function __construct(App $app)
    {
        parent::__construct($app);

        $this->order = $this->app[OrderRepositoryInterface::class];
    }

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

//        $result = $this
//            ->model
//            ->select([
//                'forums.*',
//                'subject',
//                'topics.id AS topic_id',
//                'topics.slug AS topic_slug',
//                'posts.user_id',
//                'posts.created_at',
//                'posts.user_name AS anonymous_name',
//                'users.name AS user_name',
//                'users.photo',
//                $this->raw('users.deleted_at IS NULL AS is_active'),
//                'users.is_confirm'
//            ])
//            ->leftJoin('posts', 'posts.id', '=', 'forums.last_post_id')
//            ->leftJoin('users', 'users.id', '=', 'posts.user_id')
//            ->leftJoin('topics', 'topics.id', '=', 'posts.topic_id')
//            ->trackForum($guestId)
//            ->trackTopic($guestId)
//            ->when($parentId, function (Builder $builder) use ($parentId) {
//                return $builder->where('parent_id', $parentId);
//            })
//            ->get();

        $result = $this
            ->model
            ->trackForum($guestId)
            ->with(['post' => function (HasOne $builder) use ($guestId) {
                return $builder
                    ->select(['id', 'user_id', 'topic_id', 'forum_id', 'user_name', 'created_at', 'text'])
                    ->with([
                        'topic' => function (BelongsTo $builder) use ($guestId) {
                            return $builder->trackTopic($guestId);
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
     * @param int $userId
     * @return mixed
     */
    public function categoriesOrder($userId)
    {
        $this->applyCriteria();

        $sql = $this
            ->model
            ->select([
                'forums.*',
                'forum_orders.is_hidden',
                $this->raw('CASE WHEN forum_orders.section IS NOT NULL THEN forum_orders.section ELSE forums.section END')
            ])
            ->leftJoin('forum_orders', function (JoinClause $join) use ($userId) {
                $join->on('forum_orders.forum_id', '=', 'forums.id')
                        ->on('forum_orders.user_id', '=', $this->raw($userId));
            })
            ->whereNull('parent_id')
            ->orderByRaw('(CASE WHEN forum_orders.order IS NOT NULL THEN forum_orders.order ELSE forums.order END)');

        $parents = $sql->get();

        $this->resetModel();

        return $parents;
    }

    /**
     * @inheritdoc
     */
    public function list()
    {
        return $this->applyCriteria(function () {
            return $this->model->select('forums.id', 'name', 'slug', 'parent_id')->orderBy('forums.order')->get();
        });
    }

    /**
     * @return array
     */
    public function getTagsCloud()
    {
        return $this
            ->tags()
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
        return $this
            ->tags()
            ->whereIn('tags.name', $tags)
            ->orderBy($this->raw('COUNT(*)'), 'DESC')
            ->get()
            ->pluck('count', 'name')
            ->toArray();
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

    /**
     * @return mixed
     */
    private function tags()
    {
        return $this
            ->app
            ->make(Topic\Tag::class)
            ->select(['name', $this->raw('COUNT(*) AS count')])
            ->join('tags', 'tags.id', '=', 'tag_id')
            ->join('topics', 'topics.id', '=', 'topic_id')
                ->whereNull('topics.deleted_at')
                ->whereNull('tags.deleted_at')
            ->groupBy('name');
    }
}
