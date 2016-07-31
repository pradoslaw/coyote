<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Grids\User\StatsGrid;
use Coyote\Repositories\Contracts\PostRepositoryInterface as PostRepository;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess;
use Coyote\Services\Grid\Source\EloquentDataSource;

class StatsController extends BaseController
{
    use HomeTrait;

    /**
     * @param PostRepository $post
     * @return \Illuminate\View\View
     */
    public function index(PostRepository $post)
    {
        $post->pushCriteria(new OnlyThoseWithAccess(auth()->user()));

        $grid = $this->getGridBuilder()->createGrid(StatsGrid::class);
        $grid->setSource(new EloquentDataSource($post->takeStatsForUser($this->userId)));
        $grid->setEnablePagination(false);

        return $this->view('user.stats')->with('grid', $grid);
    }
}
