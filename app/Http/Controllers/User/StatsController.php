<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Grids\User\StatsGrid;
use Coyote\Repositories\Contracts\PostRepositoryInterface as PostRepository;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess;
use Boduch\Grid\Source\EloquentSource;

class StatsController extends BaseController
{
    use HomeTrait;

    /**
     * @param PostRepository $post
     * @return \Illuminate\View\View
     */
    public function index(PostRepository $post)
    {
        $post->pushCriteria(new OnlyThoseWithAccess($this->auth));

        $grid = $this
            ->gridBuilder()
            ->createGrid(StatsGrid::class)
            ->setSource(new EloquentSource($post->takeStatsForUser($this->userId)))
            ->setEnablePagination(false);

        return $this->view('user.stats')->with('grid', $grid);
    }
}
