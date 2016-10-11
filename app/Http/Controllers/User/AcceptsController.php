<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Grids\User\AcceptsGrid;
use Coyote\Repositories\Contracts\PostRepositoryInterface as PostRepository;
use Boduch\Grid\Source\EloquentSource;

class AcceptsController extends BaseController
{
    use HomeTrait;

    /**
     * @param PostRepository $post
     * @return \Illuminate\View\View
     */
    public function index(PostRepository $post)
    {
        $grid = $this
            ->gridBuilder()
            ->createGrid(AcceptsGrid::class)
            ->setSource(new EloquentSource($post->takeAcceptsForUser($this->userId)));

        return $this->view('user.accepts')->with('grid', $grid);
    }
}
