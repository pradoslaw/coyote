<?php

namespace Coyote\Http\Controllers\User;

use Coyote\User;
use Illuminate\Http\Request;

class ForumController extends BaseController
{
    use SettingsTrait;
    
    /**
     * @return $this
     */
    public function index()
    {
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
