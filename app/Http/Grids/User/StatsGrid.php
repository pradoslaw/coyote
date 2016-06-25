<?php

namespace Coyote\Http\Grids\User;

use Coyote\Services\Grid\Decorators\Html;
use Coyote\Services\Grid\Grid;
use Coyote\Services\Grid\Order;

class StatsGrid extends Grid
{
    public function buildGrid()
    {
        $this
            ->setDefaultOrder(new Order('posts_count', 'desc'))
            ->addColumn('subject', [
                'title' => 'Kategoria forum',
                'clickable' => function ($row) {
                    return link_to_route(
                        'forum.category',
                        $row->name,
                        [$row->slug]
                    );
                },
            ])
            ->addColumn('posts_count', [
                'title' => 'Ilość postów'
            ])
            ->addColumn('votes_count', [
                'title' => 'Oceny'
            ])
            ->addColumn('sum', [
                'title' => 'Sumuj',
                'decorators' => [(new Html())->render(function ($row) {
                    return app('form')->checkbox('count[]', $row->id, true);
                })]
            ]);
    }
}
