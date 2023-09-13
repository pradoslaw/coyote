<?php

namespace Coyote\Http\Controllers\User\Menu;

use Coyote\Domain\User\UserMenu;
use Lavary\Menu\Builder;
use Lavary\Menu\Menu;

trait SettingsMenu
{
    public function getSideMenu(): Builder
    {
        $menuItems = (new UserMenu())->settingsMenu();

        /** @var Menu $menu */
        $menu = app(Menu::class);

        return $menu->make('user.settings', function (Builder $builder) use ($menuItems) {
            foreach ($menuItems as $menuItem) {
                $builder
                  ->add($menuItem->title, [
                    'id'    => $menuItem->htmlId,
                    'route' => $menuItem->route
                  ])
                  ->prepend("<i class=\"$menuItem->htmlIcon\"></i>");
            }
        });
    }
}
