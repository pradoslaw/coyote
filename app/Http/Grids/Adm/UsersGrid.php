<?php

namespace Coyote\Http\Grids\Adm;

use Coyote\Services\Grid\Filters\FilterOperation;
use Coyote\Services\Grid\Filters\Select;
use Coyote\Services\Grid\Filters\Text;
use Coyote\Services\Grid\Grid;
use Coyote\Services\Grid\Decorators\Boolean;
use Coyote\Services\Grid\Decorators\Ip;
use Coyote\Services\Grid\Order;

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
                'filter' => new Text(FilterOperation::OPERATOR_ILIKE)
            ])
            ->addColumn('email', [
                'title' => 'E-mail',
                'filter' => new Text(FilterOperation::OPERATOR_ILIKE)
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
                'filter' => new Select(FilterOperation::OPERATOR_EQ, ['options' => $booleanOptions])
            ])
            ->addColumn('is_blocked', [
                'title' => 'Zablokowany',
                'decorators' => [new Boolean()],
                'filter' => new Select(FilterOperation::OPERATOR_EQ, ['options' => $booleanOptions])
            ])
            ->addColumn('ip', [
                'title' => 'IP',
                'decorators' => [new Ip()],
                'filter' => new Text(FilterOperation::OPERATOR_ILIKE)
            ]);
    }
}
