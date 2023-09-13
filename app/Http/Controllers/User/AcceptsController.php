<?php

namespace Coyote\Http\Controllers\User;

use Boduch\Grid\Source\EloquentSource;
use Coyote\Http\Controllers\User\Menu\AccountMenu;
use Coyote\Http\Grids\User\AcceptsGrid;
use Coyote\Repositories\Contracts\PostRepositoryInterface;
use Illuminate\View\View;

class AcceptsController extends BaseController
{
    use AccountMenu;

    public function index(PostRepositoryInterface $post): View
    {
        return $this->view('user.accepts')
          ->with('grid', $this
            ->gridBuilder()
            ->createGrid(AcceptsGrid::class)
            ->setSource(new EloquentSource($post->takeAcceptsForUser($this->userId))));
    }
}
