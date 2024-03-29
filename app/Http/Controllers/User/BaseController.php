<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Controllers\User\Menu\ProfileNavigation;

abstract class BaseController extends Controller
{
    use ProfileNavigation;

    public function __construct()
    {
        parent::__construct();
        $this->breadcrumb->push('Moje konto', route('user.home'));
    }

    /**
     * @inheritdoc
     */
    protected function view($view = null, $data = [])
    {
        $data['side_menu'] = $this->getSideMenu();
        $data['top_menu'] = $this->getUserMenu();
        $data['top_menu']->get($data['side_menu']->name)->activate();
        return parent::view($view, $data);
    }
}
