<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;
use Coyote\User;
use Illuminate\Http\Request;

class ForumController extends Controller
{
    /**
     * @return $this
     */
    public function index()
    {
        $this->breadcrumb->push('Moje konto', route('user.home'));
        $this->breadcrumb->push('Personalizacja forum', route('user.forum'));

        return $this->view('user.forum');
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function save(Request $request)
    {
        //
        return back()->with('success', 'Zmiany zostały poprawie zapisane');
    }
}
