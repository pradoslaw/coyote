<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Guide;
use Coyote\Repositories\Contracts\GuideRepositoryInterface;

class GuideRepository extends Repository implements GuideRepositoryInterface
{
    public function model()
    {
        return Guide::class;
    }

    /**
     * @inheritDoc
     */
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        return $this->applyCriteria(function () use ($perPage) {
            return $this->model->select(['id', 'title', 'created_at', 'user_id', 'views', 'votes', 'role'])->with(['user', 'tags'])->withCount('comments')->paginate($perPage);
        });
    }
}
