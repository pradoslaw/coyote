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
        $rowset = $sql->get();
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
}
