<?php

namespace Coyote\Repositories\Eloquent\Forum;

use Coyote\Repositories\Contracts\Forum\OrderRepositoryInterface;
use Coyote\Repositories\Eloquent\Repository;

class OrderRepository extends Repository implements OrderRepositoryInterface
{
    /**
     * @return \Coyote\Forum\Order
     */
    public function model()
    {
        return 'Coyote\Forum\Order';
    }

    /**
     * @param int $userId
     * @return mixed
     */
    public function takeForUser($userId)
    {
        return $this->model->select(['forums.id', 'forum_orders.section', 'hidden', 'forum_orders.order'])
                    ->join('forums', 'forums.id', '=', 'forum_id')
                    ->where('user_id', $userId)
                    ->orderBy('order')
                    ->get();
    }
}
