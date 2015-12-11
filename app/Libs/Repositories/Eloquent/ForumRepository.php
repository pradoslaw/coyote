<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\ForumRepositoryInterface;

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
                    ->select(['forums.*', 'forum_track.created_at AS forum_marked_at'])
                    ->forAll()
                    ->leftJoin('forum_track', function ($join) use ($userId, $sessionId) {
                        $join->on('forum_id', '=', 'forums.id');

                        if ($userId) {
                            $join->on('user_id', '=', \DB::raw($userId));
                        } else {
                            $join->on('session_id', '=', \DB::raw("'" . $sessionId . "'"));
                        }
                    })
                    ->orderBy('order');

        if ($parentId) {
            $sql->where('parent_id', $parentId);
        }

        // execute query and fetch all forum categories
        $parents = $this->buildTree($sql->get());

        // we must fill section field in every row just to group rows by section name.
        $section = '';
        foreach ($parents as &$parent) {
            if ($parent->section) {
                $section = $parent->section;
            } else {
                $parent->section = $section;
            }
        }

        // finally... group by sections
        return $parents->groupBy('section');
    }

    /**
     * @param \Collection $rowset
     * @return mixed
     */
    private function buildTree($rowset)
    {
        // extract only main categories
        $parents = $rowset->where('parent_id', null)->keyBy('id');

        // extract only children categories
        $children = $rowset->filter(function ($item) {
            return $item->parent_id != null;
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
        $result = $this->model->select(['id', 'name', 'path', 'parent_id'])->forAll()->orderBy('order')->get();
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
}
