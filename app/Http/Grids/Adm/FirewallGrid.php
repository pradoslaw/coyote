<?php
namespace Coyote\Http\Grids\Adm;

use Boduch\Grid\Components\EditButton;
use Boduch\Grid\Decorators\FormatDateRelative;
use Boduch\Grid\Decorators\LongText;
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
            ->addColumn('user_name', [
                'title'       => 'Dla użytkownika',
                'sortable'    => true,
                'placeholder' => 'nie podano',
                'clickable'   => fn(Firewall $ban) => link_to_route('adm.firewall.save', $ban->user_name, [$ban->id]),
                'filter'      => new Text(['operator' => FilterOperator::OPERATOR_ILIKE, 'name' => 'users.name']),
            ])
            ->addColumn('ip', [
                'title'  => 'IP',
                'filter' => new Text(['operator' => FilterOperator::OPERATOR_ILIKE, 'name' => 'firewall.ip']),
            ])
            ->addColumn('reason', [
                'title'       => 'Powód',
                'decorators'  => [new LongText()],
                'placeholder' => '--',
            ])
            ->addColumn('created_at', [
                'title'      => 'Utworzony',
                'decorators' => [new FormatDateRelative('nigdy')],
            ])
            ->addColumn('duration', [
                'title'     => 'Długość bana',
                'clickable' => function (Firewall $ban) {
                    if ($ban->expire_at === null) {
                        return '∞';
                    }
                    $diff = $ban->expire_at->diffForHumans($ban->created_at, syntax:true);
                    return "na $diff";
                },
            ])
            ->addColumn('moderator_name', [
                'title'     => 'Dany przez',
                'clickable' => fn(Firewall $ban) => link_to_route('adm.users.save', $ban->moderator_name, [$ban->moderator_id]),
            ])
            ->addRowAction(new EditButton(fn(Firewall $ban) => route('adm.firewall.save', [$ban->id])))
            ->addComponent(new CreateButton(route('adm.firewall.save'), 'Nowy ban'));
    }
}
