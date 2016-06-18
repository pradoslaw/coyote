<?php

namespace Coyote\Http\Controllers\Wiki;

use Coyote\Events\WikiWasDeleted;
use Coyote\Services\Stream\Objects\Wiki as Stream_Wiki;
use Coyote\Services\Stream\Activities\Delete as Stream_Delete;

class DeleteController extends BaseController
{
    /**
     * @param \Coyote\Wiki $wiki
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index($wiki)
    {
        $this->transaction(function () use ($wiki) {
            $this->wiki->delete($wiki->id);

            stream(
                Stream_Delete::class,
                (new Stream_Wiki())->map($wiki)
            );

            event(new WikiWasDeleted($wiki));
        });

        return back()->with('success', 'Strona została usunięta.');
    }
}
