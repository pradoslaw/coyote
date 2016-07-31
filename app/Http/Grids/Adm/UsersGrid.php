<?php

namespace Coyote\Http\Grids\Adm;

use Coyote\Services\Grid\Filters\DateRange;
use Coyote\Services\Grid\Filters\FilterOperator;
use Coyote\Services\Grid\Filters\Select;
use Coyote\Services\Grid\Filters\Text;
use Coyote\Services\Grid\Grid;
use Coyote\Services\Grid\Decorators\Boolean;
use Coyote\Services\Grid\Decorators\Ip;
use Coyote\Services\Grid\Order;
use Coyote\Services\Grid\RowActions\EditButton;

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
                'title' => 'ID',
                'sortable' => true
            ])
            ->addColumn('name', [
                'title' => 'Nazwa użytkownika',
                'sortable' => true,
                'clickable' => function ($user) {
                    /** @var \Coyote\User $user */
                    return link_to_route('adm.user.save', $user->name, [$user->id]);
                },
                'filter' => new Text(['operator' => FilterOperator::OPERATOR_ILIKE])
            ])
            ->addColumn('email', [
                'title' => 'E-mail',
                'filter' => new Text(['operator' => FilterOperator::OPERATOR_ILIKE])
            ])
            ->addColumn('created_at', [
                'title' => 'Data rejestracji'
            ])
            ->addColumn('visited_at', [
                'title' => 'Data ost. wizyty',
                'sortable' => true
            ])
            ->addColumn('is_active', [
                'title' => 'Aktywny',
                'decorators' => [new Boolean()],
                'filter' => new Select(['options' => $booleanOptions])
            ])
            ->addColumn('is_blocked', [
                'title' => 'Zablokowany',
                'decorators' => [new Boolean()],
                'filter' => new Select(['options' => $booleanOptions])
            ])
            ->addColumn('ip', [
                'title' => 'IP',
                'decorators' => [new Ip()],
                'filter' => new Text(['operator' => FilterOperator::OPERATOR_ILIKE])
            ])
            ->addRowAction(new EditButton(function ($user) {
                /** @var \Coyote\User $user */
                return route('adm.user.save', [$user->id]);
            }));
    }
}
