<?php

namespace Coyote\Http\Controllers\User\Menu;

use Coyote\Domain\User\User;
use Coyote\Domain\User\UserMenu;
use Illuminate\Contracts\Auth\Authenticatable;
use Lavary\Menu\Builder;
use Lavary\Menu\Menu;

trait AccountMenu
{
    public function getSideMenu(): Builder
    {
        $menuItems = (new UserMenu())->accountMenu($this->laravelUser());

        /** @var Menu $menu */
        $menu = app(Menu::class);

        return $menu->make('user.home', function (Builder $menu) use ($menuItems) {
            foreach ($menuItems as $menuItem) {
                $menu
                  ->add($menuItem->title, [
                    'id'        => $menuItem->htmlId ?? '',
                    'class'     => $menuItem->htmlClass ?? '',
                    'route'     => $menuItem->route,
                    'icon'      => $menuItem->htmlIcon,
                    'subscript' => $menuItem->subscript
                  ]);
            }
        });
    }

    private function laravelUser(): User
    {
        /** @var Authenticatable|\Coyote\User $user */
        $user = auth()->user();
        return new LaravelUser($user);
    }
}
