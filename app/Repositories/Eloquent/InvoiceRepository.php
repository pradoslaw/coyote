<?php

namespace Coyote\Repositories\Eloquent;

use Carbon\Carbon;
use Coyote\Invoice;
use Coyote\Repositories\Contracts\InvoiceRepositoryInterface;

class InvoiceRepository extends Repository implements InvoiceRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return Invoice::class;
    }

    /**
     * @inheritdoc
     */
    public function getNextSeq(Carbon $date): int
    {
        return (int) $this->applyCriteria(function () use ($date) {
            $seq = $this->model
                ->selectRaw('MAX(seq) AS seq')
                ->whereRaw("extract(YEAR from created_at) = ?", [$date->year])
                ->whereRaw("extract(MONTH from created_at) = ?", [$date->month])
                ->value('seq');

            return !$seq ? 1 : ($seq + 1);
        });
    }

    /**
     * @inheritdoc
     */
    public function countInvoices(Carbon $date): int
    {
        return (int) $this->applyCriteria(function () use ($date) {
            return $this->model
                ->whereNotNull('number')
                ->whereRaw("extract(YEAR from created_at) = ?", [$date->year])
                ->whereRaw("extract(MONTH from created_at) = ?", [$date->month])
                ->whereRaw("extract(DAY from created_at) = ?", [$date->day])
                ->count();
        });
    }
}
