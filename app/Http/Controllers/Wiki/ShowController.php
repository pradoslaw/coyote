<?php

namespace Coyote\Http\Controllers\Wiki;

use Illuminate\Http\Request;

class ShowController extends BaseController
{
    public function index(Request $request)
    {
        /** @var \Coyote\Wiki $wiki */
        $wiki  = $request->wiki;

        $author = $wiki->logs()->first()->user;
        $wiki->text = $this->getParser()->parse((string) $wiki->text);

        return $this->view('wiki.' . $wiki->template, [
            'wiki' => $wiki,
            'author' => $author
        ]);
    }
}
