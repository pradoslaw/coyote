<?php

namespace Coyote\Http\Controllers\User\Menu;

use Coyote\Domain\User\User;
use Coyote\Domain\User\UserMenu;
use Lavary\Menu\Builder;
use Lavary\Menu\Menu;

trait ProfileNavigation
{
    public function getUserMenu(): Builder
    {
        $userMenu = new UserMenu();
        $menuItems = $userMenu->profileNavigation($this->laravelUser());

        /** @var Menu $menu */
        $menu = app(Menu::class);

        return $menu->make('user.top', function (Builder $menu) use ($menuItems) {
            foreach ($menuItems as $menuItem) {
                $menu
                  ->add($menuItem->title, [
                    'class' => 'nav-item',
                    'route' => $menuItem->route
                  ])
                  ->nickname($menuItem->routeName)
                  ->link
                  ->attr(['class' => 'nav-link']);
            }
        });
    }

    private function laravelUser(): User
    {
        if (auth()->check()) {
            return new User(true, auth()->user()->id);
        }
        return new User(false, null);
    }
}
