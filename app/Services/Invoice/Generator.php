<?php

namespace Coyote\Services\Invoice;

use Coyote\Invoice;
use Coyote\Payment;
use Coyote\Repositories\Contracts\InvoiceRepositoryInterface as InvoiceRepository;

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
        $invoice = $this->repository->create(
            array_merge($attributes, ['currency_id' => $payment->plan->currency_id])
        );

        $invoice->items()->create([
            'description'   => $this->getDescription($payment),
            'price'         => $payment->plan->price * $payment->days,
            'vat_rate'      => $payment->plan->vat_rate
        ]);

        return $invoice;
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
