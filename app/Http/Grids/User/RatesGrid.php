<?php

namespace Coyote\Http\Grids\User;

use Coyote\Services\Grid\Decorators\DateTimeFormat;
use Coyote\Services\Grid\Grid;
use Coyote\Services\Grid\Order;

class RatesGrid extends Grid
{
    public function buildGrid()
    {
        $this
            ->setDefaultOrder(new Order('post_votes.id', 'desc'))
            ->addColumn('subject', [
                'title' => 'Temat wątku',
                'clickable' => function ($row) {
                    return link_to(
                        route('forum.topic', [$row->forum_slug, $row->topic_id, $row->topic_slug]) . '?p=' . $row->post_id . '#id' . $row->post_id,
                        $row->subject
                    );
                },
            ])
            ->addColumn('created_at', [
                'title' => 'Data napisania'
            ])
            ->addColumn('user_id', [
                'title' => 'Użytkownik',
                'clickable' => function ($row) {
                    return link_to_route('profile', $row->user_name, [$row->user_id]);
                }
            ])
            ->addColumn('voted_at', [
                'title' => 'Data wystawienia oceny',
                'decorators' => [new DateTimeFormat('Y-m-d H:i:s')]
            ]);
    }
}
