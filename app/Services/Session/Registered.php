<?php

namespace Coyote\Services\Session;

use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Repositories\Criteria\User\InSession;
use Coyote\Repositories\Criteria\WithTrashed;
use Coyote\Session;
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
        $registered = $collection->filter(function (Session $item) {
            return $item->userId !== null;
        });

        // include group name and only few columns in query
        $this->user->pushCriteria(new InSession());
        $this->user->pushCriteria(new WithTrashed());

        $result = $this->user->findMany($registered->pluck('user_id')->toArray());

        $this->user->resetCriteria();

        foreach ($result as $row) {
            foreach ($collection as &$item) {
                if ($row->user_id == $item['user_id']) {
                    $item['name'] = $row->name;
                    $item['group'] = $row->group;
                }
            }
        }

        return $collection->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);
    }
}
