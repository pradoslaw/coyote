<?php

namespace Coyote\Repositories\Contracts;

use Carbon\Carbon;

interface InvoiceRepositoryInterface extends RepositoryInterface
{
    /**
     * @param Carbon $date
     * @return int
     */
    public function getNextSeq(Carbon $date): int;

    /**
     * @param Carbon $date
     * @return int
     */
    public function countInvoices(Carbon $date): int;
}
