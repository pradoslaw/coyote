<?php

namespace Coyote\Http\Grids\Adm;

use Boduch\Grid\Decorators\DateTime;
use Boduch\Grid\Decorators\StrLimit;
use Boduch\Grid\Filters\FilterOperator;
use Boduch\Grid\Filters\Text;
use Coyote\Firewall;
use Coyote\Services\Grid\Components\CreateButton;
use Coyote\Services\Grid\Grid;
use Boduch\Grid\Decorators\Ip;
use Boduch\Grid\Order;
use Boduch\Grid\Components\EditButton;
use Coyote\Services\Grid\Components\PurgeButton;


class FirewallGrid extends Grid
{
    public function buildGrid()
    {
        $this
            ->setDefaultOrder(new Order('id', 'desc'))
            ->addColumn('id', [
                'title' => 'ID',
                'sortable' => true,
                'clickable' => function (Firewall $row) {
                    return link_to_route('adm.firewall.save', $row->id, [$row->id]);
                },
                'filter' => new Text(['operator' => FilterOperator::OPERATOR_EQ, 'name' => 'firewall.id'])
            ])
            ->addColumn('user_name', [
                'title' => 'Nazwa użytkownika',
                'sortable' => true,
                'placeholder' => '--',
                'clickable' => function (Firewall $row) {
                    return link_to_route('adm.users.save', $row->user_name, [$row->user_id]);
                },
                'filter' => new Text(['operator' => FilterOperator::OPERATOR_ILIKE, 'name' => 'users.name'])
            ])
            ->addColumn('ip', [
                'title' => 'IP',
                'decorators' => [new Ip()],
                'filter' => new Text(['operator' => FilterOperator::OPERATOR_ILIKE, 'name' => 'firewall.ip']),
            ])
            ->addColumn('expire_at', [
                'title' => 'Data przedawnienia',
                'decorators' => [new DateTime('Y-m-d')]
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
                'clickable' => function (Firewall $row) {
                    return link_to_route('adm.users.save', $row->moderator_name, [$row->moderator_id]);
                }
            ])
            ->addRowAction(new PurgeButton(function (Firewall $row) {
                return route('adm.users.judge', [$row->user_id]);
            }))
            ->addRowAction(new EditButton(function (Firewall $row) {
                return route('adm.firewall.save', [$row->id]);
            }))
            ->addComponent(
                new CreateButton(
                    route('adm.firewall.save'),
                    'Nowy'
                )
            );
    }
}
