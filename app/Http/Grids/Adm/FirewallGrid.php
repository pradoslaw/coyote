<?php
namespace Coyote\Http\Grids\Adm;

use Boduch\Grid\Components\EditButton;
use Boduch\Grid\Decorators\FormatDateRelative;
use Boduch\Grid\Decorators\StrLimit;
use Boduch\Grid\Filters\FilterOperator;
use Boduch\Grid\Filters\Text;
use Boduch\Grid\Order;
use Coyote\Firewall;
use Coyote\Services\Grid\Components\CreateButton;
use Coyote\Services\Grid\Grid;

class FirewallGrid extends Grid
{
    public function buildGrid(): void
    {
        $this
            ->setDefaultOrder(new Order('id', 'desc'))
            ->addColumn('id', [
                'title'     => 'ID',
                'sortable'  => true,
                'clickable' => fn(Firewall $row) => link_to_route('adm.firewall.save', $row->id, [$row->id]),
                'filter'    => new Text(['operator' => FilterOperator::OPERATOR_EQ, 'name' => 'firewall.id']),
            ])
            ->addColumn('user_name', [
                'title'       => 'Nazwa użytkownika',
                'sortable'    => true,
                'placeholder' => '--',
                'clickable'   => fn(Firewall $row) => link_to_route('adm.users.save', $row->user_name, [$row->user_id]),
                'filter'      => new Text(['operator' => FilterOperator::OPERATOR_ILIKE, 'name' => 'users.name']),
            ])
            ->addColumn('ip', [
                'title'  => 'IP',
                'filter' => new Text(['operator' => FilterOperator::OPERATOR_ILIKE, 'name' => 'firewall.ip']),
            ])
            ->addColumn('expire_at', [
                'title'      => 'Wygasa',
                'decorators' => [new FormatDateRelative('permaban')],
            ])
            ->addColumn('reason', [
                'title'      => 'Powód',
                'decorators' => [new StrLimit()],
            ])
            ->addColumn('created_at', [
                'title'      => 'Dodany',
                'decorators' => [new FormatDateRelative('nigdy')],
            ])
            ->addColumn('moderator_name', [
                'title'     => 'Założony przez',
                'clickable' => fn(Firewall $row) => link_to_route('adm.users.save', $row->moderator_name, [$row->moderator_id]),
            ])
            ->addRowAction(new EditButton(fn(Firewall $row) => route('adm.firewall.save', [$row->id])))
            ->addComponent(new CreateButton(
                route('adm.firewall.save'),
                'Nowy ban',
            ));
    }
}
