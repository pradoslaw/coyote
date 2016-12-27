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
    public function findAllVisibleIds($userId)
    {
        if ($userId === null) {
            return [];
        }

        return $this
            ->model
            ->where('user_id', $userId)
            ->where('is_hidden', 0)
            ->pluck('forum_id')
            ->toArray();
    }
}
