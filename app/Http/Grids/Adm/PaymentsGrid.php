<?php

namespace Coyote\Http\Grids\Adm;

use Boduch\Grid\Components\ShowButton;
use Boduch\Grid\Order;
use Coyote\Payment;
use Coyote\Services\Grid\Grid;

class PaymentsGrid extends Grid
{
    public function buildGrid()
    {
        $this
            ->setDefaultOrder(new Order('created_at', 'desc'))
            ->addColumn('created_at', [
                'title' => 'Data dodania',
                'sortable' => true,
                'decorator' => [$this->getDateTimeDecorator()]
            ])
            ->addColumn('job.title', [
                'title' => 'Ogłoszenie',
                'clickable' => function (Payment $payment) {
                    return link_to_route('job.offer', $payment->job->title, [$payment->job->id, $payment->job->slug]);
                }
            ])
            ->addColumn('status_id', [
                'title' => 'Status',
                'render' => function (Payment $payment) {
                    return Payment::getPaymentStatusesList()[$payment->status_id];
                }
            ])
            ->addColumn('invoice_id', [
                'title' => 'Faktura',
                'render' => function (Payment $payment) {
                    if (!$payment->invoice || !$payment->invoice->number) {
                        return '--';
                    }

                    return link_to_route('adm.payments.invoice', $payment->invoice->number, [$payment->id]);
                }
            ])
            ->addRowAction(new ShowButton(function (Payment $payment) {
                return route('adm.payments.show', [$payment->id]);
            }));
    }
}
