<?php

namespace Coyote\Http\Grids\Adm;

use Boduch\Grid\Decorators\Ip;
use Boduch\Grid\Filters\FilterOperator;
use Boduch\Grid\Filters\Text;
use Boduch\Grid\Row;
use Carbon\Carbon;
use Coyote\Services\Grid\Grid;
use Boduch\Grid\Order;
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
                'clickable' => function ($session) {
                    if ($session['user_id']) {
                        return link_to_route('adm.users.save', $session['name'], [$session['user_id']]);
                    } else {
                        return $session['robot'] ?: '--';
                    }
                },
                'filter' => new Text(['operator' => FilterOperator::OPERATOR_ILIKE])
            ])
            ->addColumn('created_at', [
                'title' => 'Data logowania',
                'sortable' => true,
                'render' => function ($row) {
                    return Carbon::createFromTimestamp($row['created_at']);
                },
                'decorator' => [$this->getDateTimeDecorator()]
            ])
            ->addColumn('updated_at', [
                'title' => 'Ostatnia aktywność',
                'sortable' => true,
                'render' => function ($row) {
                    return Carbon::createFromTimestamp($row['updated_at']);
                },
                'decorator' => [$this->getDateTimeDecorator()]
            ])
            ->addColumn('ip', [
                'title' => 'IP',
                'decorators' => [new Ip()],
                'filter' => new Text(['operator' => FilterOperator::OPERATOR_ILIKE, 'name' => 'sessions.ip'])
            ])
            ->addColumn('url', [
                'title' => 'Strona',
                'render' => function ($row) {
                    return link_to($row['url']);
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
