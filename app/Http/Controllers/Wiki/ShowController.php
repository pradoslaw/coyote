<?php

namespace Coyote\Http\Controllers\Wiki;

use Illuminate\Http\Request;

class ShowController extends BaseController
{
    public function index(Request $request)
    {
        /** @var \Coyote\Wiki $request->wiki */
        return $this->view('wiki.' . $request->wiki->template, [
            'wiki' => $request->wiki
        ]);
    }
}
