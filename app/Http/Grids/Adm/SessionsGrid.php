<?php

namespace Coyote\Http\Grids\Adm;

use Boduch\Grid\Decorators\FormatDateRelative;
use Boduch\Grid\Filters\FilterOperator;
use Boduch\Grid\Filters\Text;
use Boduch\Grid\Order;
use Boduch\Grid\Row;
use Carbon\Carbon;
use Coyote\Services\Grid\Grid;
use Coyote\Session;
use Jenssegers\Agent\Agent;

class SessionsGrid extends Grid
{
    public function buildGrid(): void
    {
        $this
            ->setDefaultOrder(new Order('updated_at', 'desc'))
            ->addColumn('name', [
                'title'     => 'Nazwa użytkownika',
                'sortable'  => true,
                'clickable' => function (Session $session) {
                    if ($session->userId) {
                        return link_to_route('adm.users.save', $session->name, [$session->userId]);
                    }
                    return $session->robot ?: '--';
                },
                'filter'    => new Text(['operator' => FilterOperator::OPERATOR_ILIKE]),
            ])
            ->addColumn('created_at', [
                'title'      => 'Data logowania',
                'sortable'   => true,
                'render'     => fn(Session $session) => Carbon::createFromTimestamp($session->createdAt),
                'decorators' => [new FormatDateRelative('nigdy')],
            ])
            ->addColumn('updated_at', [
                'title'      => 'Ostatnia aktywność',
                'sortable'   => true,
                'render'     => fn(Session $session) => Carbon::createFromTimestamp($session->updatedAt),
                'decorators' => [new FormatDateRelative('nigdy')],
            ])
            ->addColumn('ip', [
                'title'  => 'IP',
                'filter' => new Text(['operator' => FilterOperator::OPERATOR_ILIKE, 'name' => 'sessions.ip']),
            ])
            ->addColumn('path', [
                'title'  => 'Strona',
                'render' => fn(Session $session) => link_to($session->path, \parse_url($session->path, \PHP_URL_PATH)),
                'filter' => new Text(['operator' => FilterOperator::OPERATOR_ILIKE]),
            ])
            ->addColumn('browser', [
                'title' => 'Urządzenie',
            ])
            ->after(function (Row $row) {
                $agent = new Agent();
                $agent->setUserAgent($row->raw('browser'));
                $row->get('browser')->setValue($agent->platform() . ', ' . $agent->browser());
            });
    }
}
