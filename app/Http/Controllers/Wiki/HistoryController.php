<?php

namespace Coyote\Http\Controllers\Wiki;

class HistoryController extends BaseController
{
    /**
     * @param \Coyote\Wiki $wiki
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index($wiki)
    {
        return $this->view('wiki.history');
    }
}
