<?php

namespace Coyote\Http\Controllers\Wiki;

class PurgeController extends BaseController
{
    /**
     * @param \Coyote\Wiki $wiki
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index($wiki)
    {
        $this->getParser()->cache->forget($wiki->text);

        return back()->with('success', 'Strona została usunięta z cache.');
    }
}
