<?php

namespace Coyote\Http\Controllers\Wiki;

use Boduch\Grid\Source\EloquentSource;
use Coyote\Http\Grids\Wiki\LogGrid;

class LogController extends BaseController
{
    /**
     * @param \Coyote\Wiki $wiki
     * @return \Illuminate\View\View
     */
    public function index($wiki)
    {
        $this->breadcrumb->push('Historia zmian');

        $source = new EloquentSource(
            $this
                ->wiki
                ->getLogBuilder()
                ->where('wiki_log.wiki_id', $wiki->wiki_id)
                ->where('wiki_paths.path_id', $wiki->id)
        );

        /** @var LogGrid $grid */
        $grid = $this
            ->gridBuilder()
            ->createGrid(LogGrid::class)
            ->setSource($source)
            ->setEnablePagination(false)
            ->addComparisionButtons();

        return $this->view('wiki.log')->with(compact('grid', 'wiki'));
    }
}
