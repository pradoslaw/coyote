<?php

namespace Coyote\Http\Controllers\Adm;

class ExitController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        $this->request->session()->remove('admin');

        return redirect()->route('home');
    }
}
