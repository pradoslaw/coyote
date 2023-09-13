<?php

namespace Coyote\Http\Controllers\User\Menu;

use Coyote\Domain\User\UserMenu;
use Lavary\Menu\Builder;
use Lavary\Menu\Menu;

trait AccountMenu
{
    public function getSideMenu(): Builder
    {
        $menuItems = (new UserMenu())->accountMenu();

        /** @var Menu $menu */
        $menu = app(Menu::class);

        return $menu->make('user.home', function (Builder $menu) use ($menuItems) {
            foreach ($menuItems as $menuItem) {
                $menu
                  ->add($menuItem->title, [
                    'id'    => $menuItem->htmlId ?? '',
                    'class' => $menuItem->htmlClass ?? '',
                    'route' => $menuItem->route
                  ])
                  ->prepend("<i class=\"$menuItem->htmlIcon\"></i>");
            }

            $user = auth()->user();
            $menu
              ->find('btn-pm')
              ->append(' <small>(' . $user->pm_unread . '/' . $user->pm . ')</small>');
        });
    }
}
