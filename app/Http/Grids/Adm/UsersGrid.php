<?php

namespace Coyote\Http\Grids\Adm;

use Boduch\Grid\Components\EditButton;
use Boduch\Grid\Decorators\Boolean;
use Boduch\Grid\Decorators\FormatDateRelative;
use Boduch\Grid\Filters\FilterOperator;
use Boduch\Grid\Filters\Select;
use Boduch\Grid\Filters\Text;
use Boduch\Grid\Order;
use Coyote\Services\Grid\Components\FirewallButton;
use Coyote\Services\Grid\Grid;
use Coyote\User;

class UsersGrid extends Grid
{
    const YES = 1;
    const NO = 0;

    public function buildGrid()
    {
        $booleanOptions = [self::YES => 'Tak', self::NO => 'Nie'];

        $this
            ->setDefaultOrder(new Order('id', 'desc'))
            ->addColumn('id', [
                'title'    => 'ID',
                'sortable' => true,
            ])
            ->addColumn('name', [
                'title'     => 'Użytkownik',
                'sortable'  => true,
                'clickable' => fn(User $user) => link_to_route('adm.users.show', $user->name, [$user->id]),
                'filter'    => new Text(['operator' => FilterOperator::OPERATOR_ILIKE]),
            ])
            ->addColumn('email', [
                'title'  => 'E-mail',
                'filter' => new Text(['operator' => FilterOperator::OPERATOR_ILIKE]),
            ])
            ->addColumn('created_at', [
                'title'      => 'Rejestracja',
                'decorators' => [new FormatDateRelative('nigdy')],
            ])
            ->addColumn('visited_at', [
                'title'      => 'Ostatnia wizyta',
                'sortable'   => true,
                'decorators' => [new FormatDateRelative('nigdy')],
            ])
            ->addColumn('is_active', [
                'title'      => 'Aktywny',
                'decorators' => [new Boolean()],
            ])
            ->addColumn('is_blocked', [
                'title'      => 'Zablokowany',
                'decorators' => [new Boolean()],
                'filter'     => new Select(['options' => $booleanOptions]),
            ])
            ->addColumn('reputation', [
                'title'    => 'Reputacja',
                'sortable' => true,
            ])
            ->addRowAction(new FirewallButton(fn(User $user) => route('adm.firewall.save', ['user' => $user->id, 'ip' => $user->ip])))
            ->addRowAction(new EditButton(fn(User $user) => route('adm.users.save', [$user->id])));
    }
}
