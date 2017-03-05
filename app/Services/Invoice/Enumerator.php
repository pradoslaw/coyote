<?php

namespace Coyote\Services\Invoice;

use Carbon\Carbon;
use Coyote\Invoice;
use Coyote\Repositories\Contracts\InvoiceRepositoryInterface as InvoiceRepository;
use Coyote\Repositories\Criteria\Invoice\ForMonth;

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
     * Set the invoice number.
     *
     * @param Invoice $invoice
     */
    public function setup(Invoice $invoice)
    {
        $invoice->number = $this->getNumber();
    }

    /**
     * @return string
     */
    protected function getNumber(): string
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
}
