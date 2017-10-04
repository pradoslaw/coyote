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
     * @param Calculator $calculator
     * @return Invoice
     */
    public function create(array $attributes, Payment $payment, Calculator $calculator): Invoice
    {
        /** @var \Coyote\Invoice $invoice */
        $invoice = $this->repository->create(
            array_merge($attributes, ['currency_id' => $payment->plan->currency_id])
        );

        $invoice->items()->create([
            'description'   => $this->getDescription($payment),
            'price'         => $calculator->netPrice(),
            'vat_rate'      => $calculator->vatRate
        ]);

        return $invoice;
    }

    /**
     * @param Payment $payment
     * @return string
     */
    private function getDescription(Payment $payment): string
    {
        return sprintf('Ogłoszenie %s w serwisie %s (%d dni)', $payment->plan->name, config('app.name'), $payment->days);
    }
}
