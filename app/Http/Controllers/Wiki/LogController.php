<?php

namespace Coyote\Http\Controllers\Wiki;

use Boduch\Grid\Source\EloquentSource;
use Coyote\Http\Grids\Wiki\LogGrid;
use Illuminate\Http\Request;

class LogController extends BaseController
{
    /**
     * @param \Coyote\Wiki $wiki
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index($wiki, Request $request)
    {
        $this->breadcrumb->push('Historia zmian', route('wiki.log', [$wiki->id]));

        $source = new EloquentSource(
            $this
                ->wiki
                ->getLogBuilder()
                ->where('wiki_log.wiki_id', $wiki->wiki_id)
                ->where('wiki_paths.path_id', $wiki->id),
        );

        /** @var LogGrid $grid */
        $grid = $this
            ->gridBuilder()
            ->createGrid(LogGrid::class)
            ->setSource($source)
            ->setEnablePagination(false)
            ->addComparisionButtons();

        $diff = $this->diff($wiki, $request);

        return $this->view('wiki.log')
            ->with([
                'grid' => $grid,
                'wiki' => $wiki,
                'diff' => $diff,
            ]);
    }

    /**
     * @param \Coyote\Wiki $wiki
     * @param Request $request
     * @return array
     */
    private function diff($wiki, Request $request)
    {
        if (!$request->filled('r1') || !$request->filled('r2')) {
            return [];
        }

        return [
            'before' => htmlspecialchars($wiki->logs->find($request->get('r1'), ['text'])->text),
            'after'  => htmlspecialchars($wiki->logs->find($request->get('r2'), ['text'])->text),
        ];
    }
}
