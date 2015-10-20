<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;

class SettingsController extends Controller
{
    /**
     * @return Response
     */
    public function getIndex()
    {
        $this->breadcrumb->push('Ustawienia', '/User/Settings');

        return parent::view('user/settings');
    }

    public function postIndex()
    {
    }
}
