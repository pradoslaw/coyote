<?php

namespace Coyote\Http\Grids\Adm;

use Coyote\Services\Grid\Decorators\DateTimeFormat;
use Coyote\Services\Grid\Decorators\StrLimit;
use Coyote\Services\Grid\Filters\FilterOperation;
use Coyote\Services\Grid\Filters\Text;
use Coyote\Services\Grid\Grid;
use Coyote\Services\Grid\Decorators\Ip;
use Coyote\Services\Grid\Order;
use Coyote\Services\Grid\RowActions\EditButton;

class FirewallGrid extends Grid
{
    public function buildGrid()
    {
        $this
            ->setDefaultOrder(new Order('id', 'desc'))
            ->addColumn('id', [
                'title' => 'ID',
                'sortable' => true,
                'clickable' => function ($row) {
                    /** @var \Coyote\Firewall $row */
                    return link_to_route('adm.firewall.save', $row->id, [$row->id]);
                }
            ])
            ->addColumn('user_name', [
                'title' => 'Nazwa użytkownika',
                'sortable' => true,
                'clickable' => function ($row) {
                    if (empty($row->user_name)) {
                        return '--';
                    }

                    return link_to_route('adm.user.save', $row->user_name, [$row->user_id]);
                },
                'filter' => new Text(FilterOperation::OPERATOR_ILIKE)
            ])
            ->addColumn('ip', [
                'title' => 'IP',
                'decorators' => [new Ip()],
                'filter' => new Text(FilterOperation::OPERATOR_ILIKE)
            ])
            ->addColumn('expire_at', [
                'title' => 'Data przedawnienia',
                'decorators' => [new DateTimeFormat('Y-m-d')]
            ])
            ->addColumn('reason', [
                'title' => 'Powód',
                'decorators' => [new StrLimit()]
            ])
            ->addColumn('created_at', [
                'title' => 'Data utworzenia'
            ])
            ->addColumn('moderator_name', [
                'title' => 'Założony przez',
                'clickable' => function ($row) {
                    return link_to_route('adm.user.save', $row->moderator_name, [$row->moderator_id]);
                }
            ])
            ->addRowAction(new EditButton(function ($data) {
                return route('adm.firewall.save', [$data->id]);
            }));
    }
}
