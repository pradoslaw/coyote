<?php

namespace Coyote\Http\Grids\Wiki;

use Boduch\Grid\Order;
use Coyote\Services\Grid\Decorators\TextSize;
use Coyote\Services\Grid\Decorators\WikiLogHeadline;
use Coyote\Services\Grid\Grid;

class LogGrid extends Grid
{
    public function buildGrid()
    {
        $this
            ->setDefaultOrder(new Order('wiki_log.created_at', 'desc'))
            ->addColumn('user_id', [
                'title' => 'Użytkownik',
                'clickable' => function ($row) {
                    return link_to_route('profile', $row->user_name, [$row->user_id], ['data-user-id' => $row->user_id]);
                }
            ])
            ->addColumn('comment', [
                'title' => 'Komentarz',
                'decorators' => [new WikiLogHeadline()]
            ])
            ->addColumn('length', [
                'title' => 'Rozmiar',
                'decorators' => [new TextSize()]
            ])
            ->addColumn('diff', [
                'title' => 'Różnica',
                'decorators' => [new TextSize()]
            ])
            ->addColumn('created_at', [
                'Data modyfikacji',
                'decorators' => [$this->getDateTimeDecorator()]
            ]);
    }
}
