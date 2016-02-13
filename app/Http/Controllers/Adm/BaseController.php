<?php

namespace Coyote\Http\Controllers\Adm;

use Coyote\Http\Controllers\Controller;
use Menu;

/**
 * Class BaseController
 * @package Coyote\Http\Controllers\Adm
 */
class BaseController extends Controller
{
    /**
     * @return mixed
     */
    protected function menu()
    {
        return Menu::make('adm', function ($menu) {
            $dashboard = $menu->add('Strona główna', ['route' => 'adm.dashboard']);
            $dashboard->prepend('<i class="fa fa-desktop fa-fw"></i>');

            $user = $menu->add('Użytkownicy', ['route' => 'adm.user']);
            $user->prepend('<i class="fa fa-user fa-fw"></i>');

            $forum = $menu->add('Forum', []);
            $forum->link->attr(['data-toggle' => "collapse", 'aria-expanded' => "false", 'aria-controls' => "menu-forum"]);
            $forum->link->href('#menu-forum');

            $forum->prepend('<i class="fa fa-comments fa-fw"></i>');
            $forum->append('<i class="arrow fa fa-angle-left pull-right"></i>');

            $forum->add('Kategorie', ['route' => 'adm.forum.category']);
            $forum->add('Uprawnienia', ['route' => 'adm.forum.access']);
        });
    }
}
