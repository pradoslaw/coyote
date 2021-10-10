<?php

namespace Coyote\Http\Controllers\Wiki;

use Coyote\Events\WikiDeleted;
use Coyote\Services\Stream\Objects\Wiki as Stream_Wiki;
use Coyote\Services\Stream\Activities\Delete as Stream_Delete;
use Coyote\Services\Stream\Activities\Unlink as Stream_Unlink;

class DeleteController extends BaseController
{
    /**
     * @param \Coyote\Wiki $wiki
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($wiki)
    {
        $this->transaction(function () use ($wiki) {
            // get all copies of article
            $paths = $this->wiki->findAllBy('wiki_id', $wiki->wiki_id);

            // undo reputation points
            app('reputation.wiki.create')->undo($wiki->wiki_id);
            app('reputation.wiki.update')->undo($wiki->wiki_id);

            stream(
                Stream_Delete::class,
                (new Stream_Wiki())->map($wiki)
            );

            foreach ($paths as $path) {
                $this->wiki->unlink($path->id);
                event(new WikiDeleted($path));
            }
        });

        return back()->with('success', 'Strona została usunięta.');
    }

    /**
     * @param \Coyote\Wiki $wiki
     * @return \Illuminate\Http\RedirectResponse
     */
    public function unlink($wiki)
    {
        $this->transaction(function () use ($wiki) {
            $this->wiki->unlink($wiki->id);

            stream(
                Stream_Unlink::class,
                (new Stream_Wiki())->map($wiki)
            );

            event(new WikiDeleted($wiki));
        });

        return redirect()->route('home')->with('success', 'Kopia strony została usunięta.');
    }
}
