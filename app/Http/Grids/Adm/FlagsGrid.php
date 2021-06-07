<?php

namespace Coyote\Http\Grids\Adm;

use Boduch\Grid\Decorators\DateTimeLocalized;
use Boduch\Grid\Decorators\StrLimit;
use Boduch\Grid\Decorators\Url;
use Boduch\Grid\Filters\FilterOperator;
use Boduch\Grid\Filters\Text;
use Coyote\Flag;
use Coyote\Services\Grid\Grid;
use Boduch\Grid\Order;
use Boduch\Grid\Row;

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
            ->addColumn('users.name', [
                'title' => 'Nazwa użytkownika',
                'sortable' => true,
                'placeholder' => '--',
                'clickable' => function (Flag $row) {
                    return link_to_route('adm.users.save', $row->user_name, [$row->user_id]);
                },
                'filter' => new Text(['operator' => FilterOperator::OPERATOR_ILIKE])
            ])
            ->addColumn('url', [
                'title' => 'URL',
                'filter' => new Text(['operator' => FilterOperator::OPERATOR_ILIKE]),
                'decorators' => [new Url()]
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
                'clickable' => function (Flag $row) {
                    return link_to_route('adm.users.save', $row->moderator_name, [$row->moderator_id]);
                },
                'placeholder' => '--'
            ])
            ->after(function (Row $row) {
                if (!empty($row->raw('deleted_at'))) {
                    $row->class = 'strikeout';
                }
            });
    }
}
