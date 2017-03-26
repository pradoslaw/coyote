<?php

namespace Coyote\Services\Session;

use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Repositories\Criteria\User\InSession;
use Illuminate\Support\Collection;

class Registered
{
    /**
     * @var UserRepository
     */
    private $user;

    /**
     * @param UserRepository $user
     */
    public function __construct(UserRepository $user)
    {
        $this->user = $user;
    }

    /**
     * @param Collection $collection
     * @return Collection
     */
    public function setup(Collection $collection)
    {
        $registered = $collection->filter(function ($item) {
            return $item['user_id'] !== null;
        });

        // include group name and only few columns in query
        $this->user->pushCriteria(new InSession());
        $result = $this->user->findMany($registered->pluck('user_id')->toArray());

        foreach ($result as $row) {
            foreach ($collection as $key => $item) {
                if ($row->user_id == $item['user_id']) {
                    $collection[$key] = array_merge($item, $row->toArray());
                }
            }
        }

        return $collection;
    }
}
