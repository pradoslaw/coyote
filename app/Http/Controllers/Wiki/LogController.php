<?php

namespace Coyote\Http\Controllers\Wiki;

use Boduch\Grid\Source\EloquentSource;
use Coyote\Http\Grids\Wiki\LogGrid;
use Coyote\Wiki;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogController extends BaseController
{
    public function index(Wiki $wiki, Request $request): View
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

        return $this->view('wiki.log')->with([
            'grid' => $grid,
            'wiki' => $wiki,
            'diff' => $diff,
        ]);
    }

    private function diff(Wiki $wiki, Request $request): array
    {
        if ($request->filled('r1') && $request->filled('r2')) {
            return $this->diffBetween($wiki,
                $request->get('r1'),
                $request->get('r2'));
        }
        return [];
    }

    private function diffBetween(Wiki $wiki, string $beforeLogId, string $afterLogId): array
    {
        $before = $wiki->logs->find($beforeLogId);
        $after = $wiki->logs->find($afterLogId);
        if ($before && $after) {
            return [
                'before' => \htmlSpecialChars($before->text),
                'after'  => \htmlSpecialChars($after->text),
            ];
        }
        return [];
    }
}
