<?php

namespace Coyote\Services\Invoice;

use Carbon\Carbon;
use Coyote\Invoice;
use Coyote\Repositories\Criteria\Invoice\ForMonth;
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
        $invoice->number = $this->getNumber();
        $invoice->save();

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
            $count = explode('/', $last->number)[1];
        }

        return sprintf('%s/%d/%d/%02d', '4P', $count + 1, $date->month, $date->year);
    }
}
