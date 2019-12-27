<?php

namespace Coyote\Services\Invoice;

use Carbon\Carbon;
use Coyote\Invoice;
use Coyote\Repositories\Contracts\InvoiceRepositoryInterface as InvoiceRepository;

class Enumerator
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
     * Set up invoice number.
     *
     * @param Invoice $invoice
     * @return Invoice
     */
    public function enumerate(Invoice $invoice): Invoice
    {
        $date = Carbon::now();
        $seq = $this->repository->countInvoices($date) + 1;

        $invoice->number = $this->formatNumber($seq, $date);
        $invoice->save();

        return $invoice;
    }

    /**
     * @param int $seq
     * @param Carbon $date
     * @return string
     */
    private function formatNumber(int $seq, Carbon $date): string
    {
        return sprintf('%02d%02d%02d-%d', $date->year, $date->month, $date->day, $seq);
    }
}
