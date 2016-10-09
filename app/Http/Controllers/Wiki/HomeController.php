<?php

namespace Coyote\Http\Controllers\Wiki;

use Boduch\Grid\Source\EloquentDataSource;
use Coyote\Http\Grids\Wiki\LogGrid;

class HomeController extends BaseController
{
    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->breadcrumb->push('Kompendium', route('wiki.home'));

        $grid = $this
            ->gridBuilder()
            ->createGrid(LogGrid::class)
            ->setSource(new EloquentDataSource($this->wiki->getLogQuery()));

        return $this->view('wiki.home')->with('grid', $grid);
    }
}
