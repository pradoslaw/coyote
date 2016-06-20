<?php

namespace Coyote\Repositories\Criteria\Forum;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Coyote\Repositories\Contracts\RepositoryInterface;
use Coyote\Repositories\Criteria\Criteria;
use Coyote\User;

class OnlyThoseWithAccess extends Criteria
{
    /**
     * @var array
     */
    private $groupsId = [];

    /**
     * @param \Coyote\User|null $user
     */
    public function __construct($user = null)
    {
        if ($user instanceof User) {
            $this->groupsId = $user->getGroupsId();
        }
    }

    /**
     * @param $model
     * @param RepositoryInterface $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        return $model->whereNested(function ($sub) use ($model, $repository) {
            $sub->whereNotExists(function ($sub) use ($repository) {
                return $sub->select('forum_id')
                    ->from('forum_access')
                    ->where('forum_access.forum_id', '=', $repository->raw('forums.id'));
            });

            if (!empty($this->groupsId)) {
                $sub->orWhereExists(function ($sub) use ($repository) {
                    return $sub->select('forum_id')
                        ->from('forum_access')
                        ->whereIn('group_id', $this->groupsId)
                        ->where('forum_access.forum_id', '=', $repository->raw('forums.id'));
                });
            }
        });
    }
}
