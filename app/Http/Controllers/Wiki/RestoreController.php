<?php

namespace Coyote\Http\Controllers\Wiki;

use Coyote\Events\WikiSaved;
use Coyote\Services\Stream\Objects\Wiki as Stream_Wiki;
use Coyote\Services\Stream\Activities\Restore as Stream_Restore;

class RestoreController extends BaseController
{
    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index($id)
    {
        $this->transaction(function () use ($id) {
            $this->wiki->restore($id);
            $wiki = $this->wiki->find($id);

            stream(
                Stream_Restore::class,
                (new Stream_Wiki())->map($wiki)
            );

            event(new WikiSaved($wiki));
        });

        return back()->with('success', 'Strona została przywrócona.');
    }
}
