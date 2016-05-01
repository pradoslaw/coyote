<?php

namespace Coyote\Repositories\Contracts;

interface PollRepositoryInterface extends RepositoryInterface
{
    /**
     * @param int $id
     * @param mixed $data
     * @return \Coyote\Poll
     */
    public function updateOrCreate($id, $data);
}
