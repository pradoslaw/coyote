<?php

namespace Coyote\Http\Grids\Adm;

use Boduch\Grid\Decorators\Ip;
use Boduch\Grid\Filters\FilterOperator;
use Boduch\Grid\Filters\Text;
use Boduch\Grid\Row;
use Carbon\Carbon;
use Coyote\Services\Grid\Grid;
use Boduch\Grid\Order;
use Coyote\Session;
use Jenssegers\Agent\Agent;

class SessionsGrid extends Grid
{
    public function buildGrid()
    {
        $this
            ->setDefaultOrder(new Order('updated_at', 'desc'))
            ->addColumn('name', [
                'title' => 'Nazwa użytkownika',
                'sortable' => true,
                'clickable' => function (Session $session) {
                    if ($session->userId) {
                        return link_to_route('adm.users.save', $session->name, [$session->userId]);
                    } else {
                        return $session->robot ?: '--';
                    }
                },
                'filter' => new Text(['operator' => FilterOperator::OPERATOR_ILIKE])
            ])
            ->addColumn('created_at', [
                'title' => 'Data logowania',
                'sortable' => true,
                'render' => function (Session $session) {
                    return Carbon::createFromTimestamp($session->createdAt);
                },
                'decorator' => [$this->getDateTimeDecorator()]
            ])
            ->addColumn('updated_at', [
                'title' => 'Ostatnia aktywność',
                'sortable' => true,
                'render' => function (Session $session) {
                    return Carbon::createFromTimestamp($session->updatedAt);
                },
                'decorator' => [$this->getDateTimeDecorator()]
            ])
            ->addColumn('ip', [
                'title' => 'IP',
                'decorators' => [new Ip()],
                'filter' => new Text(['operator' => FilterOperator::OPERATOR_ILIKE, 'name' => 'sessions.ip'])
            ])
            ->addColumn('path', [
                'title' => 'Strona',
                'render' => function (Session $session) {
                    return link_to($session->path);
                },
                'filter' => new Text(['operator' => FilterOperator::OPERATOR_ILIKE])
            ])
            ->addColumn('browser', [
                'title' => 'Przeglądarka'
            ])
            ->addColumn('platform', [
                'title' => 'System operacyjny'
            ])
            ->addColumn('user_agent', [
                'title' => 'User-agent',
                'filter' => new Text(['operator' => FilterOperator::OPERATOR_ILIKE, 'name' => 'sessions.browser'])
            ])
            ->after(function (Row $row) {
                $agent = new Agent();
                $agent->setUserAgent($row->raw('browser'));

                $row->get('platform')->setValue($agent->platform());
                $row->get('browser')->setValue($agent->browser());
                $row->get('user_agent')->setValue($row->raw('browser'));
            });
    }
}
