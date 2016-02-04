<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\ForumRepositoryInterface;
use Coyote\Forum\Track as Forum_Track;
use Coyote\Topic\Track as Topic_Track;
use Coyote\Topic;

class ForumRepository extends Repository implements ForumRepositoryInterface
{
    /**
     * @return \Coyote\Forum
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

        $sql = $this->model
                    ->select([
                        'forums.*',
                        'forum_track.marked_at AS forum_marked_at',
                        'topic_track.marked_at AS topic_marked_at',
                        'subject',
                        'topics.id AS topic_id',
                        'topics.path AS topic_path',
                        'posts.user_id',
                        'posts.created_at',
                        'posts.user_name AS anonymous_name',
                        'users.name AS user_name',
                        'users.photo',
                        'is_active',
                        'is_confirm'
                    ])
                    ->leftJoin('forum_track', function ($join) use ($userId, $sessionId) {
                        $join->on('forum_id', '=', 'forums.id');

                        if ($userId) {
                            $join->on('forum_track.user_id', '=', \DB::raw($userId));
                        } else {
                            $join->on('forum_track.session_id', '=', \DB::raw("'" . $sessionId . "'"));
                        }
                    })
                    ->leftJoin('posts', 'posts.id', '=', 'forums.last_post_id')
                    ->leftJoin('users', 'users.id', '=', 'posts.user_id')
                    ->leftJoin('topics', 'topics.id', '=', 'posts.topic_id')
                    ->leftJoin('topic_track', function ($join) use ($userId, $sessionId) {
                        $join->on('topic_track.topic_id', '=', 'topics.id');

                        if ($userId) {
                            $join->on('topic_track.user_id', '=', \DB::raw($userId));
                        } else {
                            $join->on('topic_track.session_id', '=', \DB::raw("'" . $sessionId . "'"));
                        }
                    })
                    ->orderBy('order');

        if ($parentId) {
            $sql->where('parent_id', $parentId);
        }

        $result = $sql->get();

        foreach ($result as &$row) {
            $row->forum_unread = $row->created_at > $row->forum_marked_at;
            $row->topic_unread = $row->created_at > $row->topic_marked_at && $row->created_at > $row->forum_marked_at;
            $row->route = route('forum.topic', [$row->path, $row->topic_id, $row->topic_path]);
        }

        // execute query and fetch all forum categories
        $parents = $this->buildTree($result, $parentId);

        // we must fill section field in every row just to group rows by section name.
        $section = '';
        foreach ($parents as &$parent) {
            if ($parent->section) {
                $section = $parent->section;
            } else {
                $parent->section = $section;
            }

            if (isset($parent->subs)) {
                foreach ($parent->subs as $child) {
                    $parent->topics += $child->topics;
                    $parent->posts += $child->posts;

                    if ($child->created_at > $parent->created_at) {
                        if ($child->forum_unread) {
                            $parent->forum_unread = true;
                        }

                        $parent->last_post_id = $child->last_post_id;
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
        return $parents->groupBy('section');
    }

    /**
     * @param \Collection $rowset
     * @param int $parentId
     * @return mixed
     */
    private function buildTree($rowset, $parentId = null)
    {
        // extract only main categories
        $parents = $rowset->where('parent_id', $parentId)->keyBy('id');

        // extract only children categories
        $children = $rowset->filter(function ($item) use ($parentId) {
            return $item->parent_id != $parentId;
        })->groupBy('parent_id');

        // we merge children with parent element
        foreach ($children as $parentId => $child) {
            $parents[$parentId]->subs = $child;
        }

        return $parents;
    }

    /**
     * Builds up a category list that can be shown in a <select>
     *
     * @return array
     */
    public function forumList()
    {
        $this->applyCriteria();

        $list = [];
        $result = $this->model->select(['id', 'name', 'path', 'parent_id'])->orderBy('order')->get();
        $tree = $this->buildTree($result);

        foreach ($tree as $parent) {
            $list[$parent->path] = $parent->name;

            if (isset($parent->subs)) {
                foreach ($parent->subs as $child) {
                    $list[$child->path] = str_repeat('&nbsp;', 4) . $child->name;
                }
            }
        }

        return $list;
    }

    /**
     * @param $forumId
     * @param $userId
     * @param $sessionId
     * @return mixed
     */
    public function markTime($forumId, $userId, $sessionId)
    {
        $sql = Forum_Track::select('marked_at')->where('forum_id', $forumId);

        if ($userId) {
            $sql->where('user_id', $userId);
        } else {
            $sql->where('session_id', $sessionId);
        }

        return $sql->pluck('marked_at');
    }

    /**
     * @return array
     */
    public function getTagClouds()
    {
        return (new Topic\Tag())
                ->select(['name', \DB::raw('COUNT(*) AS count')])
                ->join('tags', 'tags.id', '=', 'tag_id')
                ->join('topics', 'topics.id', '=', 'topic_id')
                    ->whereNull('topics.deleted_at')
                    ->whereNull('tags.deleted_at')
                ->groupBy('name')
                ->orderBy(\DB::raw('COUNT(*)'), 'DESC')
                ->limit(10)
                ->get()
                ->lists('count', 'name')
                ->toArray();
    }

    /**
     * @param array $tags
     * @return mixed
     */
    public function getTagsWeight(array $tags)
    {
        return (new Topic\Tag())
                ->select(['name', \DB::raw('COUNT(*) AS count')])
                ->join('tags', 'tags.id', '=', 'tag_id')
                ->join('topics', 'topics.id', '=', 'topic_id')
                    ->whereIn('tags.name', $tags)
                    ->whereNull('topics.deleted_at')
                    ->whereNull('tags.deleted_at')
                ->groupBy('name')
                ->get()
                ->lists('count', 'name')
                ->toArray();
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
        Forum_Track::updateOrCreate($attributes, $attributes + ['marked_at' => \DB::raw('NOW()'), 'forum_id' => $forumId]);
        $track = new Topic_Track();

        foreach ($attributes as $key => $value) {
            $track = $track->where($key, $value);
        }

        $track->delete();
    }
}
