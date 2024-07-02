<?php
namespace Coyote\Http\Controllers\User;

use Boduch\Grid\Source\EloquentSource;
use Coyote\Http\Grids\User\StatsGrid;
use Coyote\Repositories\Contracts\PostRepositoryInterface;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess;
use Illuminate\View\View;

class StatsController extends BaseController
{
    public function index(PostRepositoryInterface $post): View
    {
        $this->breadcrumb->push('Statystyki moich postów', route('user.stats'));
        $post->pushCriteria(new OnlyThoseWithAccess($this->auth));
        return $this->view('user.stats')->with('grid', $this
            ->gridBuilder()
            ->createGrid(StatsGrid::class)
            ->setSource(new EloquentSource($post->takeStatsForUser($this->userId)))
            ->setEnablePagination(false));
    }
}
