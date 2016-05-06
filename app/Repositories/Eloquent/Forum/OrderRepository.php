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
     * @param array $data
     */
    public function saveForUser($userId, array $data)
    {
        $this->model->where('user_id', $userId)->delete();
        
        foreach ($data as $row) {
            $this->model->create($row + ['user_id' => $userId]);
        }
    }
}
