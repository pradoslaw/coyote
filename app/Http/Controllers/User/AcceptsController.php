<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Grids\User\AcceptsGrid;
use Coyote\Repositories\Contracts\PostRepositoryInterface as PostRepository;
use Boduch\Grid\Source\EloquentDataSource;

class AcceptsController extends BaseController
{
    use HomeTrait;

    /**
     * @param PostRepository $post
     * @return \Illuminate\View\View
     */
    public function index(PostRepository $post)
    {
        $grid = $this->gridBuilder()->createGrid(AcceptsGrid::class);
        $grid->setSource(new EloquentDataSource($post->takeAcceptsForUser($this->userId)));

        return $this->view('user.accepts')->with('grid', $grid);
    }
}
