<?php

namespace Coyote\Http\Controllers\User;

use Lavary\Menu\Menu;

trait HomeTrait
{
    public function getSideMenu()
    {
        $collection = [
            [
                'id' => 'btn-start',
                'route' => 'user.home',
                'icon' => 'fa-map-marker',
                'label' => 'Start'
            ],
            [
                'id' => 'btn-notifies',
                'route' => 'user.notifications',
                'icon' => 'fa-bell',
                'label' => 'Powiadomienia'
            ],
            [
                'id' => 'btn-pm',
                'route' => 'user.pm',
                'icon' => 'fa-envelope',
                'label' => 'Wiadomości prywatne'
            ],
            [
                'id' => 'btn-favorites',
                'route' => 'user.favorites',
                'icon' => 'fa-heart',
                'label' => 'Ulubione i obserwowane strony'
            ],
            [
                'id' => 'btn-rates',
                'route' => 'user.rates',
                'icon' => 'fa-star-half',
                'label' => 'Oceny moich postów'
            ],
            [
                'id' => 'btn-stats',
                'route' => 'user.stats',
                'icon' => 'fa-chart-bar',
                'label' => 'Statystyki moich postów'
            ],
            [
                'id' => 'btn-accepts',
                'route' => 'user.accepts',
                'icon' => 'fa-check',
                'label' => 'Zaakceptowane odpowiedzi'
            ],
            [
                'class' => 'text-danger',
                'route' => 'user.delete',
                'icon' => 'fa-times',
                'label' => 'Usuń konto'
            ]
        ];

        return app(Menu::class)->make('user.home', function ($menu) use ($collection) {
            foreach ($collection as $row) {
                $menu->add($row['label'], ['route' => $row['route'], 'id' => $row['id'] ?? '', 'class' => $row['class'] ?? ''])
                        ->prepend('<i class="fa fa-fw ' . $row['icon'] . '"></i>');
            }

            $menu->find('btn-pm')->append(' <small>(' . auth()->user()->pm_unread . '/' . auth()->user()->pm . ')</small>');
        });
    }
}
