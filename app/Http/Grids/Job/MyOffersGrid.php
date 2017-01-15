<?php

namespace Coyote\Http\Grids\Job;

use Coyote\Services\Grid\Grid;
use Boduch\Grid\Order;
use Carbon\Carbon;
use Coyote\Job;

class MyOffersGrid extends Grid
{
    public function buildGrid()
    {
        $this
            ->setDefaultOrder(new Order('created_at', 'desc'))
            ->setEmptyMessage('Brak aktualnie wyświetlanych ofert pracy.')
            ->addColumn('title', [
                'title' => 'Tytuł oferty',
                'clickable' => function (Job $job) {
                    return link_to_route('job.offer', $job->title, [$job->id, $job->slug]);
                }
            ])
            ->addColumn('firm_name', [
                'title' => 'Nazwa firmy',
                'placeholder' => '--'
            ])
            ->addColumn('created_at', [
                'title' => 'Data dodania',
                'sortable' => true,
                'decorators' => [$this->getDateTimeDecorator()]
            ])
            ->addColumn('deadline_at', [
                'title' => 'Do końca',
                'sortable' => true,
                'render' => function (Job $job) {
                    return Carbon::now()->diffInDays(Carbon::parse($job->deadline_at)) . ' dni';
                }
            ]);
    }
}
