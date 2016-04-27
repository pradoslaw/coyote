<?php

namespace Coyote\Http\Controllers\Adm;

class DashboardController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        return $this->view('adm.dashboard');
    }
}
