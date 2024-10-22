<?php

namespace Coyote\Http\Grids\User;

use Boduch\Grid\Order;
use Coyote\Services\Grid\Grid;

class RatesGrid extends Grid
{
    public function buildGrid()
    {
        $this
            ->setDefaultOrder(new Order('post_votes.id', 'desc'))
            ->addColumn('title', [
                'title'     => 'Tytuł wątku',
                'clickable' => function ($row) {
                    return link_to(
                        route('forum.topic', [$row->forum_slug, $row->topic_id, $row->topic_slug]) . '?p=' . $row->post_id . '#id' . $row->post_id,
                        $row->title,
                    );
                },
            ])
            ->addColumn('created_at', [
                'title' => 'Data napisania',
            ])
            ->addColumn('user_id', [
                'title'     => 'Użytkownik',
                'clickable' => fn($row) => link_to_route('profile', $row->user_name, [$row->user_id]),
            ])
            ->addColumn('voted_at', [
                'title' => 'Data wystawienia oceny',
            ]);
    }
}
