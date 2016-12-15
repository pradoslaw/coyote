<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\ForumRepositoryInterface;
use Coyote\Forum\Track as Forum_Track;
use Coyote\Topic\Track as Topic_Track;
use Coyote\Topic;
use Coyote\Forum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;

class ForumRepository extends Repository implements ForumRepositoryInterface
{
    use UserTrait;

    /**
     * @return string
     */
    public function model()
    {
        return 'Coyote\Forum';
    }

    /**
     * Gets categories grouped by sections. You need to pass either $userId or $sessionId (for anonymous users)
     *
     * @param int $userId
     * @param string $sessionId
     * @param null|int $parentId
     * @return mixed
     */
    public function groupBySections($userId, $sessionId, $parentId = null)
    {
        $this->applyCriteria();

        $result = $this
            ->model
            ->select([
                'forums.*',
                'subject',
                'topics.id AS topic_id',
                'topics.slug AS topic_slug',
                'posts.user_id',
                'posts.created_at',
                'posts.user_name AS anonymous_name',
                'users.name AS user_name',
                'users.photo',
                'is_active',
                'is_confirm'
            ])
            ->leftJoin('posts', 'posts.id', '=', 'forums.last_post_id')
            ->leftJoin('users', 'users.id', '=', 'posts.user_id')
            ->leftJoin('topics', 'topics.id', '=', 'posts.topic_id')
            ->trackForum($userId, $sessionId)
            ->trackTopic($userId, $sessionId)
            ->when($parentId, function (Builder $builder) use ($parentId) {
                return $builder->where('parent_id', $parentId);
            })
            ->get();

        // loop for each category (even subcategories)
        foreach ($result as &$row) {
            if (empty($row->forum_marked_at)) {
                $row->forum_marked_at = $this->firstVisit($userId, $sessionId);
            }

            // are there any new posts (since I last marked category as read)?
            $row->forum_unread = $row->created_at > $row->forum_marked_at;
            $row->topic_unread = $row->created_at > $row->topic_marked_at && $row->forum_unread;
            $row->route = route('forum.topic', [$row->slug, $row->topic_id, $row->topic_slug]);
        }

        // execute query and fetch all forum categories
        $parents = $this->buildNested($result, $parentId);
        $parents = $this->fillUpSectionNames($parents);

        foreach ($parents as &$parent) {
            if (isset($parent->children)) {
                foreach ($parent->children as $child) {
                    $parent->topics += $child->topics;
                    $parent->posts += $child->posts;

                    // created_at contains last post's created date.
                    if ($child->created_at > $parent->created_at) {
                        if ($child->forum_unread) {
                            $parent->forum_unread = true;
                        }

                        $parent->last_post_id = $child->last_post_id;
                        $parent->topic_unread = $child->topic_unread;
                        $parent->created_at = $child->created_at;
                        $parent->user_id = $child->user_id;
                        $parent->photo = $child->photo;
                        $parent->is_active = $child->is_active;
                        $parent->is_confirm = $child->is_confirm;
                        $parent->user_name = $child->user_name;
                        $parent->anonymous_name = $child->anonymous_name;
                        $parent->subject = $child->subject;
                        $parent->route = $child->route;
                    }
                }
            }
        }

        // finally... group by sections
        $sections = $parents->groupBy('section');
        $this->resetModel();

        return $sections;
    }

    /**
     * @param int $userId
     * @return mixed
     */
    public function getOrderForUser($userId)
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

        $result = $this->fillUpSectionNames($parents)->groupBy('section');
        $this->resetModel();

        return $result;
    }

    /**
     * Get restricted access forums.
     *
     * @return int[]
     */
    public function getRestricted()
    {
        return (new Forum\Access)->groupBy('forum_id')->get(['forum_id'])->pluck('forum_id')->toArray();
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
     * @param Collection $rowset
     * @param int $parentId
     * @return Collection
     */
    private function buildNested($rowset, $parentId = null)
    {
        // extract only main categories
        $parents = $rowset->where('parent_id', $parentId)->keyBy('id');

        // extract only children categories
        $children = $rowset->filter(function ($item) use ($parentId) {
            return $item->parent_id != $parentId;
        })->groupBy('parent_id');

        // we merge children with parent element
        foreach ($children as $parentId => $child) {
            if (!empty($parents[$parentId])) {
                $parents[$parentId]->children = $child;
            }
        }

        return $parents;
    }

    /**
     * Builds up a category list that can be shown in a <select>
     *
     * @param string $key
     * @return array
     */
    public function choices($key = 'slug')
    {
        $this->applyCriteria();

        $choices = [];
        $result = $this->model->select(['forums.id', 'name', 'slug', 'parent_id'])->orderBy('forums.order')->get();
        $tree = $this->buildNested($result);

        foreach ($tree as $parent) {
            $choices[$parent->$key] = $parent->name;

            if (isset($parent->children)) {
                foreach ($parent->children as $child) {
                    $choices[$child->$key] = str_repeat('&nbsp;', 4) . $child->name;
                }
            }
        }

        $this->resetModel();

        return $choices;
    }

    /**
     * Forum categories as flatten array od models.
     *
     * @return \Coyote\Forum[]
     */
    public function flatten()
    {
        $result = $this->model->select()->orderBy('order')->get();
        $nested = $this->buildNested($result);

        $flatten = [];

        foreach ($nested as $parent) {
            $flatten[] = $parent;

            if (isset($parent->children)) {
                foreach ($parent->children as $child) {
                    $flatten[] = $child;
                }
            }
        }

        return $flatten;
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
            ->lists('count', 'name')
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
            ->lists('count', 'name')
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

    /**
     * Mark forum as read
     *
     * @param $forumId
     * @param $userId
     * @param $sessionId
     */
    public function markAsRead($forumId, $userId, $sessionId)
    {
        // builds data to update
        $attributes = ['forum_id' => $forumId] + ($userId ? ['user_id' => $userId] : ['session_id' => $sessionId]);
        // execute a query...
        Forum_Track::updateOrCreate($attributes, $attributes + ['marked_at' => $this->raw('NOW()'), 'forum_id' => $forumId]);
        $track = new Topic_Track();

        foreach ($attributes as $key => $value) {
            $track = $track->where($key, $value);
        }

        $track->delete();
    }
}
