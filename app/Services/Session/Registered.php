<?php

namespace Coyote\Services\Session;

use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
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
        $collection = $collection->filter(fn(Session $item) => $item->robot === '');
        $registered = $collection->filter(fn(Session $item) => $item->userId !== null);

        $this->user->pushCriteria(new WithTrashed());

        $result = $this->user->findMany($registered->pluck('user_id')->toArray(), ['id', 'name', 'group_name']);

        $this->user->resetCriteria();

        foreach ($result as $row) {
            foreach ($collection as &$item) {
                if ($row->id == $item['user_id']) {
                    $item['name'] = $row->name;
                    $item['group'] = $row->group_name;
                }
            }
        }

        return $collection->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);
    }
}
