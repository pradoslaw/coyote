<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Grids\User\RatesGrid;
use Coyote\Repositories\Contracts\PostRepositoryInterface as PostRepository;
use Boduch\Grid\Source\EloquentSource;

class RatesController extends BaseController
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
            ->createGrid(RatesGrid::class)
            ->setSource(new EloquentSource($post->takeRatesForUser($this->userId)));

        return $this->view('user.rates')->with('grid', $grid);
    }
}
