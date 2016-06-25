<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Grids\User\RatesGrid;
use Coyote\Repositories\Contracts\PostRepositoryInterface as PostRepository;
use Coyote\Services\Grid\Source\EloquentDataSource;

class RatesController extends BaseController
{
    use HomeTrait;

    /**
     * @param PostRepository $post
     * @return \Illuminate\View\View
     */
    public function index(PostRepository $post)
    {
        $grid = $this->getGrid()->createGrid(RatesGrid::class);
        $grid->setSource(new EloquentDataSource($post->takeRatesForUser($this->userId)));

        return $this->view('user.rates')->with('grid', $grid);
    }
}
