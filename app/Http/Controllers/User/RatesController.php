<?php

namespace Coyote\Http\Controllers\User;

use Boduch\Grid\Source\EloquentSource;
use Coyote\Http\Controllers\User\Menu\AccountMenu;
use Coyote\Http\Grids\User\RatesGrid;
use Coyote\Repositories\Contracts\PostRepositoryInterface;
use Illuminate\View\View;

class RatesController extends BaseController
{
    use AccountMenu;

    public function index(PostRepositoryInterface $post): View
    {
        return $this->view('user.rates')
          ->with('grid', $this
            ->gridBuilder()
            ->createGrid(RatesGrid::class)
            ->setSource(new EloquentSource($post->takeRatesForUser($this->userId))));
    }
}
