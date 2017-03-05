<?php

namespace Coyote\Services\Invoice;

use Coyote\Invoice;
use Coyote\Payment;
use Coyote\Repositories\Contracts\InvoiceRepositoryInterface as InvoiceRepository;
use Carbon\Carbon;
use Coyote\Repositories\Criteria\Invoice\ForMonth;

class Generator
{
    /**
     * @var InvoiceRepository
     */
    protected $repository;

    /**
     * @param InvoiceRepository $repository
     */
    public function __construct(InvoiceRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param array $attributes
     * @param Payment $payment
     * @return Invoice
     */
    public function create(array $attributes, Payment $payment): Invoice
    {
        /** @var \Coyote\Invoice $invoice */
        $invoice = $this->repository->create($attributes + ['number' => $this->getNumber()]);

        $invoice->items()->create([
            'description'   => $this->getDescription($payment),
            'price'         => $payment->plan->price * $payment->days,
            'vat_rate'      => $payment->plan->vat_rate,
            'currency_id'   => $payment->plan->currency_id
        ]);

        return $invoice;
    }

    /**
     * @return string
     */
    private function getNumber(): string
    {
        $date = Carbon::now();

        $this->repository->pushCriteria(new ForMonth($date));
        $last = $this->repository->last();

        $count = 0;

        if ($last) {
            $count = last(explode('/', $last->number));
        }

        return sprintf('%s/%d/%02d/%d', '4P', $date->year, $date->month, $count + 1);
    }

    /**
     * @param Payment $payment
     * @return string
     */
    private function getDescription(Payment $payment): string
    {
        return sprintf('%s (%d dni)', $payment->plan->name, $payment->days);
    }
}
