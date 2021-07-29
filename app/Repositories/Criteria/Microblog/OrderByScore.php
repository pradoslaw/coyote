<?php

namespace Coyote\Repositories\Criteria\Microblog;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;

class OrderByScore extends OrderById
{
    /**
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @param Repository $repository
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply($model, Repository $repository)
    {
        if ($this->withPremium) {
            $model = $model->orderBy('microblogs.is_sponsored', 'DESC');
        }

        return
            $model
            ->orderByDesc('score');
//                ->orderByRaw('votes * 10 + (EXTRACT(EPOCH FROM microblogs.created_at::timestamp) - 1380153600) / 3600 desc');
    }
}
