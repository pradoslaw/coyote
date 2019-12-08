<?php

namespace Coyote\Http\Controllers\User;

use Lavary\Menu\Menu;

trait SettingsTrait
{
    public function getSideMenu()
    {
        $collection = [
            [
                'id' => 'btn-start',
                'route' => 'user.settings',
                'icon' => 'fa-cog',
                'label' => 'Podstawowa konfiguracja'
            ],
            [
                'id' => 'btn-visits',
                'route' => 'user.skills',
                'icon' => 'fa-wrench',
                'label' => 'Umiejętności'
            ],
            [
                'id' => 'btn-notifies',
                'route' => 'user.security',
                'icon' => 'fa-lock',
                'label' => 'Bezpieczeństwo'
            ],
            [
                'id' => 'btn-pm',
                'route' => 'user.password',
                'icon' => 'fa-key',
                'label' => 'Zmiana hasła'
            ],
            [
                'id' => 'btn-favorites',
                'route' => 'user.notifications.settings',
                'icon' => 'fa-bell',
                'label' => 'Ustawienia powiadomień'
            ],
            [
                'id' => 'btn-profiles',
                'route' => 'user.forum',
                'icon' => 'fa-comments',
                'label' => 'Personalizacja forum'
            ]
        ];

        return app(Menu::class)->make('user.settings', function ($menu) use ($collection) {
            foreach ($collection as $row) {
                $menu->add($row['label'], ['route' => $row['route'], 'id' => $row['id']])
                        ->prepend('<i class="fa fa-fw ' . $row['icon'] . '"></i>');
            }
        });
    }
}
