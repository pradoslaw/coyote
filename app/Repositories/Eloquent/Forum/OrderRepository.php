<?php

namespace Coyote\Repositories\Eloquent\Forum;

use Coyote\Repositories\Contracts\Forum\OrderRepositoryInterface;
use Coyote\Repositories\Eloquent\Repository;

class OrderRepository extends Repository implements OrderRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return 'Coyote\Forum\Order';
    }

    /**
     * @inheritdoc
     */
    public function saveForUser($userId, array $data)
    {
        $this->deleteForUser($userId);

        foreach ($data as $row) {
            $this->model->create($row + ['user_id' => $userId]);
        }
    }

    /**
     * @inheritdoc
     */
    public function deleteForUser($userId)
    {
        $this->model->where('user_id', $userId)->delete();
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
            ->leftJoin('forums', 'parent_id', '=', 'forum_orders.forum_id')
            ->get();

        return array_filter(array_unique(
            array_merge($result->pluck('forum_id')->toArray(), $result->pluck('child_forum_id')->toArray())
        ));
    }
}
