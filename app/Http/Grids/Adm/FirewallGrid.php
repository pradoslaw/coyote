<?php

namespace Coyote\Http\Grids\Adm;

use Coyote\Services\Grid\Filters\FilterOperation;
use Coyote\Services\Grid\Filters\Text;
use Coyote\Services\Grid\Grid;
use Coyote\Services\Grid\Decorators\Ip;
use Coyote\Services\Grid\Order;

class FirewallGrid extends Grid
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
                    return link_to_route('adm.user.save', $user->name, [$user->user_id]);
                },
                'filter' => new Text(FilterOperation::OPERATOR_ILIKE)
            ])
            ->addColumn('ip', [
                'title' => 'IP',
                'decorators' => [new Ip()],
                'filter' => new Text(FilterOperation::OPERATOR_ILIKE)
            ])
            ->addColumn('expire_at', [
                'title' => 'Data przedawnienia'
            ])
            ->addColumn('reason', [
                'title' => 'Powód'
            ])
            ->addColumn('created_at', [
                'title' => 'Data utworzenia'
            ])
            ->addColumn('moderator_name', [
                'title' => 'Założony przez'
            ]);
    }
}
