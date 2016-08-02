<?php

namespace Coyote\Http\Grids\Adm;

use Coyote\Services\Grid\Decorators\Ip;
use Coyote\Services\Grid\Decorators\StrLimit;
use Coyote\Services\Grid\Filters\FilterOperator;
use Coyote\Services\Grid\Filters\Text;
use Coyote\Services\Grid\Grid;
use Coyote\Services\Grid\Order;
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
                'clickable' => function ($row) {
                    if ($row->user_id) {
                        return link_to_route('adm.user.save', $row->name, [$row->user_id]);
                    } else {
                        $agent = new Agent();

                        return $agent->isRobot($row->browser) ? $agent->robot() : '--';
                    }
                },
                'filter' => new Text(['operator' => FilterOperator::OPERATOR_ILIKE])
            ])
            ->addColumn('created_at', [
                'title' => 'Data logowania',
                'sortable' => true
            ])
            ->addColumn('updated_at', [
                'title' => 'Ostatnia aktywność',
                'sortable' => true
            ])
            ->addColumn('ip', [
                'title' => 'IP',
                'decorators' => [new Ip()]
            ])
            ->addColumn('url', [
                'title' => 'Strona',
                'render' => function ($row) {
                    return link_to($row->url);
                }
            ])
            ->addColumn('browser', [
                'title' => 'Przeglądarka'
            ])
            ->addColumn('platform', [
                'title' => 'System operacyjny'
            ])
            ->addColumn('user_agent', [
                'title' => 'User-agent',
                'decorator' => [new StrLimit(65)]
            ])
            ->each(function ($row) {
                /** @var $row \Coyote\Services\Grid\Row */
                $agent = new Agent();
                $agent->setUserAgent($row->raw('browser'));

                $row->get('platform')->setValue($agent->platform());
                $row->get('browser')->setValue($agent->browser());
                $row->get('user_agent')->setValue(str_limit($row->raw('browser'), 65));
            });
    }
}
