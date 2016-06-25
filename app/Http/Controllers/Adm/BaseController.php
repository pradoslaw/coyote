<?php

namespace Coyote\Http\Controllers\Adm;

use Coyote\Http\Controllers\Controller;
use Lavary\Menu\Menu;

/**
 * Class BaseController
 * @package Coyote\Http\Controllers\Adm
 */
class BaseController extends Controller
{
    /**
     * @return \Lavary\Menu\Builder
     */
    protected function buildMenu()
    {
        return $this->getMenuFactory()->make('adm', function ($menu) {
            $html = app('html');
            $fa = function ($icon) use ($html) {
                return $html->tag('i', '', ['class' => "fa $icon"]);
            };

            /** @var \Lavary\Menu\Builder $menu */
            $menu->add('Strona główna', ['route' => 'adm.dashboard'])->prepend($fa('fa-desktop fa-fw'));
            $menu->add('Użytkownicy', ['route' => 'adm.user'])->prepend($fa('fa-user fa-fw'));
            $menu->add('Bany', ['route' => 'adm.firewall'])->prepend($fa('fa-ban fa-fw'));

            $forum = $menu->add('Forum', []);
            $forum->link->attr(['data-toggle' => "collapse", 'aria-expanded' => "false", 'aria-controls' => "menu-forum"]);
            $forum->link->href('#menu-forum');

            $forum->prepend($fa('fa-comments fa-fw'));
            $forum->append($html->tag('i', '', ['class' => 'arrow fa fa-angle-left pull-right']));

            $forum->add('Kategorie', ['route' => 'adm.forum.category']);
            $forum->add('Uprawnienia', ['route' => 'adm.forum.access']);

            $menu->add('Dziennik zdarzeń', ['route' => 'adm.stream'])->prepend($fa('fa-newspaper-o fa-fw'));
            
            $log = $menu->add('Logi', ['route' => 'adm.log'])->prepend($fa('fa-file-o fa-fw'));
            $log->link->attr(['data-toggle' => "collapse", 'aria-expanded' => "false", 'aria-controls' => "menu-log"]);
            $log->link->href('#menu-log');

            $logViewer = $this->getLogViewer();
            $files = $logViewer->getFiles();

            foreach ($files as $file) {
                $log->add($file, route('adm.log', ['file' => $file]));
            }
        });
    }

    /**
     * @return Menu
     */
    protected function getMenuFactory()
    {
        return app(Menu::class);
    }

    /**
     * @return \Coyote\Services\LogViewer\LogViewer
     */
    protected function getLogViewer()
    {
        return app('log-viewer');
    }
}
