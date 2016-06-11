<?php

namespace Coyote\Http\Grids\Adm;

use Coyote\Services\Grid\Grid;
use Coyote\Services\Grid\Decorators\Boolean;
use Coyote\Services\Grid\Decorators\Ip;
use Coyote\Services\Grid\Order;

class UsersGrid extends Grid
{
    public function buildGrid()
    {
        $this
            ->setDefaultOrder(new Order('id', 'desc'))
            ->addColumn('id', [
                'title' => 'ID',
                'sortable' => true
            ])
            ->addColumn('name', [
                'title' => 'Nazwa użytkownika',
                'sortable' => true,
                'clickable' => function ($user) {
                    /** @var \Coyote\User $user */
                    return link_to_route('adm.user.save', $user->name, [$user->id]);
                }
            ])
            ->addColumn('email', [
                'title' => 'E-mail'
            ])
            ->addColumn('created_at', [
                'title' => 'Data rejestracji'
            ])
            ->addColumn('visited_at', [
                'title' => 'Data ost. wizyty',
                'sortable' => true
            ])
            ->addColumn('is_active', [
                'title' => 'Aktywny',
                'decorators' => [new Boolean()]
            ])
            ->addColumn('is_blocked', [
                'title' => 'Zablokowany',
                'decorators' => [new Boolean()]
            ])
            ->addColumn('ip', [
                'title' => 'IP',
                'decorators' => [new Ip()]
            ]);
    }
}
