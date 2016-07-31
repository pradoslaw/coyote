<?php

namespace Coyote\Http\Grids\Adm;

use Coyote\Services\Grid\Decorators\DateTimeFormat;
use Coyote\Services\Grid\Decorators\StrLimit;
use Coyote\Services\Grid\Decorators\Url;
use Coyote\Services\Grid\Filters\FilterOperator;
use Coyote\Services\Grid\Filters\Text;
use Coyote\Services\Grid\Grid;
use Coyote\Services\Grid\Order;

class FlagsGrid extends Grid
{
    public function buildGrid()
    {
        $this
            ->setDefaultOrder(new Order('id', 'desc'))
            ->addColumn('id', [
                'title' => 'ID',
                'sortable' => true
            ])
            ->addColumn('flag_type', [
                'placeholder' => '--',
                'title' => 'Typ'
            ])
            ->addColumn('user_name', [
                'title' => 'Nazwa użytkownika',
                'sortable' => true,
                'placeholder' => '--',
                'clickable' => function ($row) {
                    return link_to_route('adm.user.save', $row->user_name, [$row->user_id]);
                },
                'filter' => new Text(['operator' => FilterOperator::OPERATOR_ILIKE])
            ])
            ->addColumn('url', [
                'title' => 'URL',
                'filter' => new Text(['operator' => FilterOperator::OPERATOR_ILIKE]),
                'decorators' => [new Url()]
            ])
            ->addColumn('created_at', [
                'title' => 'Data dodania',
                'decorators' => [new DateTimeFormat('Y-m-d')]
            ])
            ->addColumn('text', [
                'title' => 'Opis',
                'decorators' => [new StrLimit()]
            ])
            ->addColumn('created_at', [
                'title' => 'Data utworzenia'
            ])
            ->addColumn('moderator_name', [
                'title' => 'Zamknięty przez',
                'clickable' => function ($row) {
                    return link_to_route('adm.user.save', $row->moderator_name, [$row->moderator_id]);
                },
                'placeholder' => '--'
            ])
            ->each(function ($row) {
                /** @var $row \Coyote\Services\Grid\Row */
                if (!empty($row->raw('deleted_at'))) {
                    $row->class = 'strikeout';
                }
            });
    }
}
